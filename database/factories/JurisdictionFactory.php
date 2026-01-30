<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Jurisdiction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jurisdiction>
 */
final class JurisdictionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'iso_code' => fake()->unique()->lexify('???'),
            'timezone' => fake()->timezone(),
            'default_currency' => fake()->currencyCode(),
        ];
    }

    /**
     * Spain jurisdiction state
     */
    public function spain(): static
    {
        return $this->state([
            'name' => 'Spain',
            'iso_code' => 'ES',
            'timezone' => 'Europe/Madrid',
            'default_currency' => 'EUR',
        ]);
    }

    /**
     * United States jurisdiction state
     */
    public function usa(): static
    {
        return $this->state([
            'name' => 'United States',
            'iso_code' => 'US',
            'timezone' => 'America/New_York',
            'default_currency' => 'USD',
        ]);
    }

    /**
     * Colombia jurisdiction state
     */
    public function colombia(): static
    {
        return $this->state([
            'name' => 'Colombia',
            'iso_code' => 'CO',
            'timezone' => 'America/Bogota',
            'default_currency' => 'COP',
        ]);
    }
}
