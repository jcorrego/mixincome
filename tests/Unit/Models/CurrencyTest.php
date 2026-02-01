<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\FxRate;
use Illuminate\Database\QueryException;

test('currency has required fields: code, name, symbol, decimal_places', function (): void {
    $currency = Currency::factory()->create();

    expect($currency)->toBeInstanceOf(Currency::class)
        ->and($currency->code)->toBeString()->toHaveLength(3)
        ->and($currency->name)->toBeString()
        ->and($currency->symbol)->toBeString()
        ->and($currency->decimal_places)->toBeInt();
});

test('currency code is unique and rejects duplicates', function (): void {
    Currency::factory()->create(['code' => 'USD']);

    Currency::factory()->create(['code' => 'USD']);
})->throws(QueryException::class);

test('USD currency has correct metadata', function (): void {
    $this->seed(\Database\Seeders\CurrencySeeder::class);

    $usd = Currency::where('code', 'USD')->firstOrFail();

    expect($usd->code)->toBe('USD')
        ->and($usd->name)->toBe('United States Dollar')
        ->and($usd->symbol)->toBe('$')
        ->and($usd->decimal_places)->toBe(2);
});

test('EUR currency has correct metadata', function (): void {
    $this->seed(\Database\Seeders\CurrencySeeder::class);

    $eur = Currency::where('code', 'EUR')->firstOrFail();

    expect($eur->code)->toBe('EUR')
        ->and($eur->name)->toBe('Euro')
        ->and($eur->symbol)->toBe('€')
        ->and($eur->decimal_places)->toBe(2);
});

test('COP currency has correct metadata with 0 decimals', function (): void {
    $this->seed(\Database\Seeders\CurrencySeeder::class);

    $cop = Currency::where('code', 'COP')->firstOrFail();

    expect($cop->code)->toBe('COP')
        ->and($cop->name)->toBe('Colombian Peso')
        ->and($cop->symbol)->toBe('$')
        ->and($cop->decimal_places)->toBe(0);
});

test('currency has many source FxRates relationship', function (): void {
    $currency = Currency::factory()->create();
    $targetCurrency = Currency::factory()->create();

    FxRate::factory()->create([
        'from_currency_id' => $currency->id,
        'to_currency_id' => $targetCurrency->id,
    ]);

    expect($currency->sourceFxRates)->toHaveCount(1)
        ->and($currency->sourceFxRates->first())->toBeInstanceOf(FxRate::class);
});

test('currency has many target FxRates relationship', function (): void {
    $sourceCurrency = Currency::factory()->create();
    $currency = Currency::factory()->create();

    FxRate::factory()->create([
        'from_currency_id' => $sourceCurrency->id,
        'to_currency_id' => $currency->id,
    ]);

    expect($currency->targetFxRates)->toHaveCount(1)
        ->and($currency->targetFxRates->first())->toBeInstanceOf(FxRate::class);
});
