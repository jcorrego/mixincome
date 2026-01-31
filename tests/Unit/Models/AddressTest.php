<?php

declare(strict_types=1);

use App\Enums\Country;
use App\Models\Address;

// --- 2.1 Address model casts country to Country enum ---

test('Address model casts country attribute to Country enum', function (): void {
    $address = Address::factory()->create(['country' => 'US']);

    expect($address->country)->toBeInstanceOf(Country::class)
        ->and($address->country)->toBe(Country::UnitedStates);
});

// --- 2.2 Address displayLabel() method ---

test('displayLabel returns street, city (country_name) format', function (): void {
    $address = Address::factory()->create([
        'street' => '123 Main St',
        'city' => 'Miami',
        'country' => 'US',
    ]);

    expect($address->displayLabel())->toBe('123 Main St, Miami (United States)');
});

test('displayLabel handles various country codes correctly', function (string $code, string $label): void {
    $address = Address::factory()->create([
        'street' => '456 Oak Ave',
        'city' => 'Springfield',
        'country' => $code,
    ]);

    expect($address->displayLabel())->toBe("456 Oak Ave, Springfield ({$label})");
})->with([
    ['US', 'United States'],
    ['ES', 'Spain'],
    ['CO', 'Colombia'],
    ['DE', 'Germany'],
    ['FR', 'France'],
]);
