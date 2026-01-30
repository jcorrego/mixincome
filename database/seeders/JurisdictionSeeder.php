<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Jurisdiction;
use Illuminate\Database\Seeder;

final class JurisdictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jurisdiction::upsert([
            ['iso_code' => 'ES', 'name' => 'Spain', 'timezone' => 'Europe/Madrid', 'default_currency' => 'EUR'],
            ['iso_code' => 'US', 'name' => 'United States', 'timezone' => 'America/New_York', 'default_currency' => 'USD'],
            ['iso_code' => 'CO', 'name' => 'Colombia', 'timezone' => 'America/Bogota', 'default_currency' => 'COP'],
        ], uniqueBy: ['iso_code'], update: ['name', 'timezone', 'default_currency']);
    }
}
