<?php

declare(strict_types=1);

use App\Enums\Currency;

test('currency enum provides correct string codes', function (): void {
    expect(Currency::Usd->value)->toBe('USD')
        ->and(Currency::Eur->value)->toBe('EUR')
        ->and(Currency::Cop->value)->toBe('COP');
});

test('currency enum decimals() returns correct values', function (): void {
    expect(Currency::Usd->decimals())->toBe(2)
        ->and(Currency::Eur->decimals())->toBe(2)
        ->and(Currency::Cop->decimals())->toBe(0);
});

test('currency enum symbol() returns correct symbols', function (): void {
    expect(Currency::Usd->symbol())->toBe('$')
        ->and(Currency::Eur->symbol())->toBe('€')
        ->and(Currency::Cop->symbol())->toBe('$');
});

test('currency enum name() returns correct names', function (): void {
    expect(Currency::Usd->name())->toBe('United States Dollar')
        ->and(Currency::Eur->name())->toBe('Euro')
        ->and(Currency::Cop->name())->toBe('Colombian Peso');
});
