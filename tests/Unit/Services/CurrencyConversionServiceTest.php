<?php

declare(strict_types=1);

use App\Services\CurrencyConversionService;
use App\Services\FxRateService;
use App\Models\Transaction;
use App\Enums\Currency;

test('service validates transaction correctly', function () {
    $fxRateService = app(FxRateService::class);
    $service = new CurrencyConversionService($fxRateService);
    
    $transaction = Transaction::factory()->create([
        'original_currency' => 'USD',
        'amount_usd' => 1000.00,
    ]);
    
    expect($service->validateTransaction($transaction))->toBeTrue();
});

test('service can get conversion stats', function () {
    $fxRateService = app(FxRateService::class);
    $service = new CurrencyConversionService($fxRateService);
    
    $transactions = collect([
        Transaction::factory()->make(['original_currency' => 'USD', 'amount_usd' => 1000]),
        Transaction::factory()->make(['original_currency' => 'EUR', 'amount_eur' => 800]),
    ]);
    
    $stats = $service->getConversionStats($transactions, Currency::Usd);
    
    expect($stats)->toHaveKeys(['total', 'need_conversion', 'already_converted', 'same_as_original']);
    expect($stats['total'])->toBe(2);
});

test('service can check if transaction needs conversion', function () {
    $fxRateService = app(FxRateService::class);
    $service = new CurrencyConversionService($fxRateService);
    
    $transaction = Transaction::factory()->create([
        'original_currency' => 'USD',
        'amount_usd' => 1000.00,
        'amount_eur' => null,
    ]);
    
    expect($service->needsConversion($transaction, Currency::Usd))->toBeFalse();
    expect($service->needsConversion($transaction, Currency::Eur))->toBeTrue();
});