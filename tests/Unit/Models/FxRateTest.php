<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\FxRate;
use Illuminate\Database\QueryException;

test('FxRate has required fields including rate with 8 decimal precision', function (): void {
    $fxRate = FxRate::factory()->create([
        'rate' => '0.00025678',
    ]);

    expect($fxRate)->toBeInstanceOf(FxRate::class)
        ->and($fxRate->from_currency_id)->toBeInt()
        ->and($fxRate->to_currency_id)->toBeInt()
        ->and($fxRate->date)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($fxRate->rate)->toBeString()
        ->and($fxRate->source)->toBeString()
        ->and($fxRate->is_replicated)->toBeBool();
});

test('FxRate stores small rates correctly', function (): void {
    $fxRate = FxRate::factory()->create([
        'rate' => '0.00025000',
    ]);

    expect((float) $fxRate->rate)->toBe(0.00025);
});

test('FxRate enforces unique constraint on currency pair + date', function (): void {
    $from = Currency::factory()->create();
    $to = Currency::factory()->create();
    $date = now()->toDateString();

    FxRate::factory()->create([
        'from_currency_id' => $from->id,
        'to_currency_id' => $to->id,
        'date' => $date,
    ]);

    FxRate::factory()->create([
        'from_currency_id' => $from->id,
        'to_currency_id' => $to->id,
        'date' => $date,
    ]);
})->throws(QueryException::class);

test('FxRate allows different dates for same currency pair', function (): void {
    $from = Currency::factory()->create();
    $to = Currency::factory()->create();

    $rate1 = FxRate::factory()->create([
        'from_currency_id' => $from->id,
        'to_currency_id' => $to->id,
        'date' => '2024-06-14',
    ]);

    $rate2 = FxRate::factory()->create([
        'from_currency_id' => $from->id,
        'to_currency_id' => $to->id,
        'date' => '2024-06-15',
    ]);

    expect($rate1->exists)->toBeTrue()
        ->and($rate2->exists)->toBeTrue();
});

test('FxRate allows different currency pairs on same date', function (): void {
    $usd = Currency::factory()->create(['code' => 'USD']);
    $eur = Currency::factory()->create(['code' => 'EUR']);
    $cop = Currency::factory()->create(['code' => 'COP']);
    $date = '2024-06-14';

    $rate1 = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $eur->id,
        'date' => $date,
    ]);

    $rate2 = FxRate::factory()->create([
        'from_currency_id' => $usd->id,
        'to_currency_id' => $cop->id,
        'date' => $date,
    ]);

    expect($rate1->exists)->toBeTrue()
        ->and($rate2->exists)->toBeTrue();
});

test('original rate has is_replicated=false and null replicated_from_date', function (): void {
    $fxRate = FxRate::factory()->create([
        'is_replicated' => false,
        'replicated_from_date' => null,
    ]);

    expect($fxRate->is_replicated)->toBeFalse()
        ->and($fxRate->replicated_from_date)->toBeNull();
});

test('replicated rate has is_replicated=true and valid replicated_from_date', function (): void {
    $fxRate = FxRate::factory()->replicated()->create();

    expect($fxRate->is_replicated)->toBeTrue()
        ->and($fxRate->replicated_from_date)->toBeInstanceOf(Carbon\CarbonInterface::class);
});

test('FxRate belongs to source currency', function (): void {
    $from = Currency::factory()->create();
    $fxRate = FxRate::factory()->create([
        'from_currency_id' => $from->id,
    ]);

    expect($fxRate->fromCurrency)->toBeInstanceOf(Currency::class)
        ->and($fxRate->fromCurrency->id)->toBe($from->id);
});

test('FxRate belongs to target currency', function (): void {
    $to = Currency::factory()->create();
    $fxRate = FxRate::factory()->create([
        'to_currency_id' => $to->id,
    ]);

    expect($fxRate->toCurrency)->toBeInstanceOf(Currency::class)
        ->and($fxRate->toCurrency->id)->toBe($to->id);
});

test('FxRate factory creates valid rate', function (): void {
    $fxRate = FxRate::factory()->create();

    expect($fxRate)->toBeInstanceOf(FxRate::class)
        ->and($fxRate->fromCurrency)->toBeInstanceOf(Currency::class)
        ->and($fxRate->toCurrency)->toBeInstanceOf(Currency::class)
        ->and((float) $fxRate->rate)->toBeGreaterThan(0);
});

test('FxRate factory replicated state works correctly', function (): void {
    $fxRate = FxRate::factory()->replicated()->create();

    expect($fxRate->is_replicated)->toBeTrue()
        ->and($fxRate->replicated_from_date)->not->toBeNull()
        ->and($fxRate->replicated_from_date->lt($fxRate->date))->toBeTrue();
});
