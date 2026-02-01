<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\FxRate;
use Database\Seeders\CurrencySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('currencies table has correct columns', function (): void {
    expect(Schema::hasTable('currencies'))->toBeTrue()
        ->and(Schema::hasColumns('currencies', [
            'id',
            'code',
            'name',
            'symbol',
            'decimal_places',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

test('fx_rates table has correct columns', function (): void {
    expect(Schema::hasTable('fx_rates'))->toBeTrue()
        ->and(Schema::hasColumns('fx_rates', [
            'id',
            'from_currency_id',
            'to_currency_id',
            'date',
            'rate',
            'source',
            'is_replicated',
            'replicated_from_date',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

test('CurrencySeeder creates exactly 3 currencies', function (): void {
    $this->seed(CurrencySeeder::class);

    $codes = Currency::query()->pluck('code')->sort()->values()->toArray();

    expect(Currency::query()->count())->toBe(3)
        ->and($codes)->toBe(['COP', 'EUR', 'USD']);
});

test('fx_rates foreign key constraints work correctly', function (): void {
    $currency = Currency::factory()->create();
    $fxRate = FxRate::factory()->create([
        'from_currency_id' => $currency->id,
    ]);

    // Deleting referenced currency should fail due to foreign key
    expect(fn () => $currency->delete())->toThrow(QueryException::class);
});
