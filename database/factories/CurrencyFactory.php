<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
final class CurrencyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var string $words */
        $words = fake()->words(2, true);

        return [
            'code' => mb_strtoupper(fake()->unique()->lexify('???')),
            'name' => $words.' Currency',
            'symbol' => fake()->randomElement(['$', '€', '£', '¥']),
            'decimal_places' => 2,
        ];
    }

    /**
     * USD currency state.
     */
    public function usd(): static
    {
        return $this->state([
            'code' => 'USD',
            'name' => 'United States Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
        ]);
    }

    /**
     * EUR currency state.
     */
    public function eur(): static
    {
        return $this->state([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
        ]);
    }

    /**
     * COP currency state.
     */
    public function cop(): static
    {
        return $this->state([
            'code' => 'COP',
            'name' => 'Colombian Peso',
            'symbol' => '$',
            'decimal_places' => 0,
        ]);
    }
}
