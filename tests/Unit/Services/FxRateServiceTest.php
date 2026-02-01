<?php

declare(strict_types=1);

use App\Exceptions\FxRateException;
use App\Models\Currency;
use App\Models\FxRate;
use App\Services\FxRateService;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
    $this->fxRateService = resolve(FxRateService::class);
});

test('findRate returns exact rate when available', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.85000000',
    ]);

    $rate = $this->fxRateService->findRate('USD', 'EUR', $date);

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(0.85)
        ->and($rate->is_replicated)->toBeFalse();
});

test('findRate falls back to most recent rate when date unavailable', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $fridayDate = Date::parse('2024-06-14');
    $saturdayDate = Date::parse('2024-06-15');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $fridayDate,
        'rate' => '0.85000000',
    ]);

    $rate = $this->fxRateService->findRate('USD', 'EUR', $saturdayDate);

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and($rate->date->toDateString())->toBe('2024-06-14')
        ->and((float) $rate->rate)->toBe(0.85);
});

test('findRate returns null when no rate exists', function (): void {
    $rate = $this->fxRateService->findRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($rate)->toBeNull();
});

test('findRate returns null when currency code is unknown', function (): void {
    $rate = $this->fxRateService->findRate('XXX', 'USD', Date::parse('2024-06-14'));

    expect($rate)->toBeNull();
});

test('findRate returns identity rate 1.0 for same currency', function (): void {
    $rate = $this->fxRateService->findRate('USD', 'USD', Date::parse('2024-06-14'));

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(1.0);
});

test('fetchRate calls API when no local rate exists', function (): void {
    $validResponse = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<message:GenericData xmlns:message="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message" xmlns:generic="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic">
    <message:DataSet>
        <generic:Obs>
            <generic:ObsDimension value="2024-06-14"/>
            <generic:ObsValue value="0.85"/>
        </generic:Obs>
    </message:DataSet>
</message:GenericData>
XML;

    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($validResponse, 200),
    ]);

    $fxRateService = resolve(FxRateService::class);
    $rate = $fxRateService->fetchRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(0.85);
});

test('fetchRate stores rate locally after API call', function (): void {
    $validResponse = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<message:GenericData xmlns:message="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message" xmlns:generic="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic">
    <message:DataSet>
        <generic:Obs>
            <generic:ObsDimension value="2024-06-14"/>
            <generic:ObsValue value="0.85"/>
        </generic:Obs>
    </message:DataSet>
</message:GenericData>
XML;

    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($validResponse, 200),
    ]);

    $fxRateService = resolve(FxRateService::class);
    $fxRateService->fetchRate('USD', 'EUR', Date::parse('2024-06-14'));

    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();

    $storedRate = FxRate::query()->where('from_currency_id', $usd->id)
        ->where('to_currency_id', $eur->id)
        ->whereDate('date', '2024-06-14')
        ->first();

    expect($storedRate)->not->toBeNull()
        ->and((float) $storedRate->rate)->toBe(0.85);
});

test('fetchRate throws exception when API fails', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response('Server Error', 500),
    ]);

    $fxRateService = resolve(FxRateService::class);
    $fxRateService->fetchRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class);

test('fetchRate returns cached rate when available', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '0.85000000',
    ]);

    // Rate exists in DB, so HTTP should not be called
    Http::fake();

    $fxRateService = resolve(FxRateService::class);
    $rate = $fxRateService->fetchRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(0.85);

    Http::assertNothingSent();
});

test('fetchRate returns identity rate for same currency', function (): void {
    Http::fake();

    $rate = $this->fxRateService->fetchRate('USD', 'USD', Date::parse('2024-06-14'));

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(1.0)
        ->and($rate->source)->toBe('identity')
        ->and($rate->is_replicated)->toBeFalse();

    Http::assertNothingSent();
});

test('replicateRate fills weekend gap with Friday rate', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $friday = Date::parse('2024-06-14');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $friday,
        'rate' => '0.85000000',
        'is_replicated' => false,
    ]);

    $saturday = Date::parse('2024-06-15');
    $this->fxRateService->replicateRate('USD', 'EUR', $saturday);

    $saturdayRate = FxRate::query()->where('from_currency_id', $usd->id)
        ->where('to_currency_id', $eur->id)
        ->where('date', $saturday)
        ->first();

    expect($saturdayRate)->not->toBeNull()
        ->and((float) $saturdayRate->rate)->toBe(0.85)
        ->and($saturdayRate->is_replicated)->toBeTrue()
        ->and($saturdayRate->replicated_from_date->toDateString())->toBe('2024-06-14');
});

test('replicateRate throws exception when no source rate exists', function (): void {
    $saturday = Date::parse('2024-06-15');
    $this->fxRateService->replicateRate('USD', 'EUR', $saturday);
})->throws(FxRateException::class);

test('replicateRate tracks replicated_from_date correctly', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $friday = Date::parse('2024-06-14');
    $sunday = Date::parse('2024-06-16');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $friday,
        'rate' => '0.85000000',
        'is_replicated' => false,
    ]);

    $this->fxRateService->replicateRate('USD', 'EUR', $sunday);

    $sundayRate = FxRate::query()->where('from_currency_id', $usd->id)
        ->where('to_currency_id', $eur->id)
        ->where('date', $sunday)
        ->first();

    expect($sundayRate->replicated_from_date->toDateString())->toBe('2024-06-14');
});

test('convert calculates correct amount with rate', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => '2024-06-14',
        'rate' => '0.85000000',
    ]);

    $result = $this->fxRateService->convert(100.00, 'USD', 'EUR', Date::parse('2024-06-14'));

    expect($result)->toBe(85.00);
});

test('convert returns same amount for same currency', function (): void {
    $result = $this->fxRateService->convert(100.00, 'USD', 'USD', Date::parse('2024-06-14'));

    expect($result)->toBe(100.00);
});

test('convert throws exception when no rate available', function (): void {
    $this->fxRateService->convert(100.00, 'USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class);

test('convert rounds to target currency decimal places', function (): void {
    // Ensure seeder created currencies
    expect(Currency::query()->count())->toBe(3);

    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $cop = Currency::query()->where('code', 'COP')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $cop->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '4000.12345678',
    ]);

    // Use a fresh service instance
    $service = resolve(FxRateService::class);
    $result = $service->convert(100.00, 'USD', 'COP', Date::parse('2024-06-14'));

    // COP has 0 decimal places
    expect($result)->toBe(400012.0);
});

// Test 6.1: Fetch new rate manually
test('fetchRateManual creates new rate when none exists', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    $rate = $this->fxRateService->fetchRateManual($usd, $eur, $date);

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and($rate->from_currency_id)->toBe($usd->id)
        ->and($rate->to_currency_id)->toBe($eur->id)
        ->and($rate->source)->toBe('ecb')
        ->and($rate->is_replicated)->toBeFalse();
});

// Test 6.2: Prevent duplicate manual fetch
test('fetchRateManual throws exception when rate already exists', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.85000000',
    ]);

    $this->fxRateService->fetchRateManual($usd, $eur, $date);
})->throws(FxRateException::class, 'Rate already exists for this currency pair and date');

// Test 6.3: Manual fetch for replicated rate
test('fetchRateManual throws exception when ECB has no data', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $saturdayDate = Date::parse('2024-06-15'); // Saturday

    Http::fake([
        '*' => Http::response('', 404),
    ]);

    $this->fxRateService->fetchRateManual($usd, $eur, $saturdayDate);
})->throws(FxRateException::class, 'ECB has no rate for this date');

// Test 7.1: Re-fetch existing rate
test('refetchRate updates existing rate with new ECB value', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.85000000',
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    $updatedRate = $this->fxRateService->refetchRate($existingRate);

    expect($updatedRate->id)->toBe($existingRate->id)
        ->and($updatedRate->updated_at->isAfter($existingRate->updated_at))->toBeTrue();
});

// Test 7.2: Re-fetch updates rate value
test('refetchRate updates rate value when ECB returns different value', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.85000000',
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    $updatedRate = $this->fxRateService->refetchRate($existingRate);

    expect($updatedRate->id)->toBe($existingRate->id)
        ->and((float) $updatedRate->rate)->not->toBe(0.85);
});

// Test 7.3: Re-fetch updates replication status
test('refetchRate updates replicated rate to ECB-sourced', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    $replicatedRate = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.85000000',
        'is_replicated' => true,
        'replicated_from_date' => Date::parse('2024-06-13'),
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    $updatedRate = $this->fxRateService->refetchRate($replicatedRate);

    expect($updatedRate->is_replicated)->toBeFalse()
        ->and($updatedRate->replicated_from_date)->toBeNull();
});

// Test 7.4: Re-fetch when ECB still has no data
test('refetchRate throws exception when ECB still has no data', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $saturdayDate = Date::parse('2024-06-15'); // Saturday

    $rate = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $saturdayDate,
        'rate' => '0.85000000',
    ]);

    Http::fake([
        '*' => Http::response('', 404),
    ]);

    $this->fxRateService->refetchRate($rate);
})->throws(FxRateException::class, 'ECB has no rate for this date');

// Test 7.5: Re-fetch returns same value
test('refetchRate updates timestamp even when rate value unchanged', function (): void {
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $date = Date::parse('2024-06-14');

    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
        'rate' => '0.92640000',
    ]);

    $oldTimestamp = $existingRate->updated_at;

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    $updatedRate = $this->fxRateService->refetchRate($existingRate);

    expect($updatedRate->updated_at->isAfter($oldTimestamp))->toBeTrue()
        ->and((float) $updatedRate->rate)->toBe(0.9264);
});
