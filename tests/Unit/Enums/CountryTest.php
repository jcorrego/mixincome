<?php

declare(strict_types=1);

use App\Enums\Country;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

// --- 1.1 Country enum contains expected cases ---

test('Country enum contains expected cases like UnitedStates, Spain, Colombia', function (): void {
    expect(Country::UnitedStates)->toBeInstanceOf(Country::class);
    expect(Country::Spain)->toBeInstanceOf(Country::class);
    expect(Country::Colombia)->toBeInstanceOf(Country::class);
});

// --- 1.2 Country enum backing values are ISO alpha-2 codes ---

test('Country enum backing values are ISO alpha-2 codes', function (): void {
    expect(Country::UnitedStates->value)->toBe('US');
    expect(Country::Spain->value)->toBe('ES');
    expect(Country::Colombia->value)->toBe('CO');
});

// --- 1.3 Country label() returns human-readable name ---

test('Country label() returns human-readable name', function (): void {
    expect(Country::UnitedStates->label())->toBe('United States');
    expect(Country::Spain->label())->toBe('Spain');
    expect(Country::Colombia->label())->toBe('Colombia');
});

// --- 1.4 Country options() returns sorted value/label pairs ---

test('Country options() returns array of value/label pairs sorted alphabetically by label', function (): void {
    $options = Country::options();

    expect($options)->toBeArray()
        ->and($options)->not->toBeEmpty()
        ->and($options[0])->toHaveKeys(['value', 'label']);

    $labels = array_column($options, 'label');
    $sorted = $labels;
    sort($sorted);

    expect($labels)->toBe($sorted);
});

// --- 1.5 Country options() with priority codes ---

test('Country options() with priority codes puts those countries first in given order', function (): void {
    $options = Country::options(['US', 'ES', 'CO']);

    expect($options[0]['value'])->toBe('US')
        ->and($options[1]['value'])->toBe('ES')
        ->and($options[2]['value'])->toBe('CO');
});

// --- 1.6 Country enum can be used with Laravel Enum validation rule ---

test('Country enum can be used with Laravel Enum validation rule', function (): void {
    $passes = Validator::make(
        ['country' => 'US'],
        ['country' => [new Enum(Country::class)]]
    );

    expect($passes->passes())->toBeTrue();

    $fails = Validator::make(
        ['country' => 'INVALID'],
        ['country' => [new Enum(Country::class)]]
    );

    expect($fails->fails())->toBeTrue();
});
