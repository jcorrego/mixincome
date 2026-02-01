<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\FxRate;
use App\Services\FxRateService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->seed(Database\Seeders\CurrencySeeder::class);
    $this->fxRateService = app(FxRateService::class);
});

test('findRate returns exact rate when available', function (): void {
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();
    $date = Carbon::parse('2024-06-14');

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
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();
    $fridayDate = Carbon::parse('2024-06-14');
    $saturdayDate = Carbon::parse('2024-06-15');

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
    $rate = $this->fxRateService->findRate('USD', 'EUR', Carbon::parse('2024-06-14'));

    expect($rate)->toBeNull();
});

test('findRate returns identity rate 1.0 for same currency', function (): void {
    $rate = $this->fxRateService->findRate('USD', 'USD', Carbon::parse('2024-06-14'));

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

    $fxRateService = app(FxRateService::class);
    $rate = $fxRateService->fetchRate('USD', 'EUR', Carbon::parse('2024-06-14'));

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

    $fxRateService = app(FxRateService::class);
    $fxRateService->fetchRate('USD', 'EUR', Carbon::parse('2024-06-14'));

    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();

    $storedRate = FxRate::where('from_currency_id', $usd->id)
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

    $fxRateService = app(FxRateService::class);
    $fxRateService->fetchRate('USD', 'EUR', Carbon::parse('2024-06-14'));
})->throws(App\Exceptions\FxRateException::class);

test('fetchRate returns cached rate when available', function (): void {
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => Carbon::parse('2024-06-14'),
        'rate' => '0.85000000',
    ]);

    // Rate exists in DB, so HTTP should not be called
    Http::fake();

    $fxRateService = app(FxRateService::class);
    $rate = $fxRateService->fetchRate('USD', 'EUR', Carbon::parse('2024-06-14'));

    expect($rate)->toBeInstanceOf(FxRate::class)
        ->and((float) $rate->rate)->toBe(0.85);

    Http::assertNothingSent();
});

test('replicateRate fills weekend gap with Friday rate', function (): void {
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();
    $friday = Carbon::parse('2024-06-14');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $friday,
        'rate' => '0.85000000',
        'is_replicated' => false,
    ]);

    $saturday = Carbon::parse('2024-06-15');
    $this->fxRateService->replicateRate('USD', 'EUR', $saturday);

    $saturdayRate = FxRate::where('from_currency_id', $usd->id)
        ->where('to_currency_id', $eur->id)
        ->where('date', $saturday)
        ->first();

    expect($saturdayRate)->not->toBeNull()
        ->and((float) $saturdayRate->rate)->toBe(0.85)
        ->and($saturdayRate->is_replicated)->toBeTrue()
        ->and($saturdayRate->replicated_from_date->toDateString())->toBe('2024-06-14');
});

test('replicateRate throws exception when no source rate exists', function (): void {
    $saturday = Carbon::parse('2024-06-15');
    $this->fxRateService->replicateRate('USD', 'EUR', $saturday);
})->throws(App\Exceptions\FxRateException::class);

test('replicateRate tracks replicated_from_date correctly', function (): void {
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();
    $friday = Carbon::parse('2024-06-14');
    $sunday = Carbon::parse('2024-06-16');

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $friday,
        'rate' => '0.85000000',
        'is_replicated' => false,
    ]);

    $this->fxRateService->replicateRate('USD', 'EUR', $sunday);

    $sundayRate = FxRate::where('from_currency_id', $usd->id)
        ->where('to_currency_id', $eur->id)
        ->where('date', $sunday)
        ->first();

    expect($sundayRate->replicated_from_date->toDateString())->toBe('2024-06-14');
});

test('convert calculates correct amount with rate', function (): void {
    $usd = Currency::where('code', 'USD')->firstOrFail();
    $eur = Currency::where('code', 'EUR')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => '2024-06-14',
        'rate' => '0.85000000',
    ]);

    $result = $this->fxRateService->convert(100.00, 'USD', 'EUR', Carbon::parse('2024-06-14'));

    expect($result)->toBe(85.00);
});

test('convert returns same amount for same currency', function (): void {
    $result = $this->fxRateService->convert(100.00, 'USD', 'USD', Carbon::parse('2024-06-14'));

    expect($result)->toBe(100.00);
});

test('convert throws exception when no rate available', function (): void {
    $this->fxRateService->convert(100.00, 'USD', 'EUR', Carbon::parse('2024-06-14'));
})->throws(App\Exceptions\FxRateException::class);

test('convert rounds to target currency decimal places', function (): void {
    // Ensure seeder created currencies
    expect(Currency::count())->toBe(3);

    $usd = Currency::where('code', 'USD')->firstOrFail();
    $cop = Currency::where('code', 'COP')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $cop->id,
        'date' => Carbon::parse('2024-06-14'),
        'rate' => '4000.12345678',
    ]);

    // Use a fresh service instance
    $service = app(FxRateService::class);
    $result = $service->convert(100.00, 'USD', 'COP', Carbon::parse('2024-06-14'));

    // COP has 0 decimal places
    expect($result)->toBe(400012.0);
});
