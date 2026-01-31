<?php

declare(strict_types=1);

use App\Models\Address;

test('display_label returns street, city (country) format', function (): void {
    $address = Address::factory()->create([
        'street' => '123 Main St',
        'city' => 'Miami',
        'country' => 'US',
    ]);

    expect($address->display_label)->toBe('123 Main St, Miami (US)');
});

test('display_label handles various country codes correctly', function (string $country): void {
    $address = Address::factory()->create([
        'street' => '456 Oak Ave',
        'city' => 'Springfield',
        'country' => $country,
    ]);

    expect($address->display_label)->toBe("456 Oak Ave, Springfield ({$country})");
})->with(['US', 'ES', 'CO', 'DE', 'FR']);
