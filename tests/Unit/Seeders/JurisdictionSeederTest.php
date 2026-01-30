<?php

declare(strict_types=1);

use App\Models\Jurisdiction;
use Database\Seeders\JurisdictionSeeder;

test('seeder creates 3 initial jurisdictions', function (): void {
    $this->seed(JurisdictionSeeder::class);

    expect(Jurisdiction::count())->toBe(3);
});

test('seeder is idempotent', function (): void {
    $this->seed(JurisdictionSeeder::class);
    $this->seed(JurisdictionSeeder::class);

    expect(Jurisdiction::count())->toBe(3);
});

test('seeder creates correct data for Spain', function (): void {
    $this->seed(JurisdictionSeeder::class);

    $spain = Jurisdiction::where('iso_code', 'ES')->first();

    expect($spain)->not->toBeNull()
        ->and($spain->name)->toBe('Spain')
        ->and($spain->timezone)->toBe('Europe/Madrid')
        ->and($spain->default_currency)->toBe('EUR');
});

test('seeder creates correct data for USA', function (): void {
    $this->seed(JurisdictionSeeder::class);

    $usa = Jurisdiction::where('iso_code', 'US')->first();

    expect($usa)->not->toBeNull()
        ->and($usa->name)->toBe('United States')
        ->and($usa->timezone)->toBe('America/New_York')
        ->and($usa->default_currency)->toBe('USD');
});

test('seeder creates correct data for Colombia', function (): void {
    $this->seed(JurisdictionSeeder::class);

    $colombia = Jurisdiction::where('iso_code', 'CO')->first();

    expect($colombia)->not->toBeNull()
        ->and($colombia->name)->toBe('Colombia')
        ->and($colombia->timezone)->toBe('America/Bogota')
        ->and($colombia->default_currency)->toBe('COP');
});
