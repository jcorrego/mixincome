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
