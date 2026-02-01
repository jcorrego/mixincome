<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\FxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FxRate>
 */
final class FxRateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_currency_id' => Currency::factory(),
            'to_currency_id' => Currency::factory(),
            'date' => fake()->date(),
            'rate' => fake()->randomFloat(8, 0.0001, 5000),
            'source' => 'ecb',
            'is_replicated' => false,
            'replicated_from_date' => null,
        ];
    }

    /**
     * Replicated rate state (copied from previous date).
     */
    public function replicated(): static
    {
        return $this->state(function (array $attributes) {
            $date = $attributes['date'] ?? now()->toDateString();

            return [
                'is_replicated' => true,
                'replicated_from_date' => now()->parse($date)->subDay()->toDateString(),
            ];
        });
    }
}
