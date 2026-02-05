<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionCategoryType;
use App\Models\TransactionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionCategory>
 */
final class TransactionCategoryFactory extends Factory
{
    protected $model = TransactionCategory::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('????_????')),
            'name' => $this->faker->words(2, true),
            'category_type' => $this->faker->randomElement(TransactionCategoryType::cases()),
            'description' => $this->faker->sentence(),
            'is_system' => false,
        ];
    }
}
