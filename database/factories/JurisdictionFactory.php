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
}
