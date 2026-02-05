<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
final class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'name' => $this->faker->company . ' ' . $this->faker->randomElement(['Checking', 'Savings']),
            'account_type' => $this->faker->randomElement(AccountType::cases()),
            'currency_id' => Currency::inRandomOrder()->first()?->id ?? Currency::factory(),
            'account_number' => $this->faker->numerify('####-####-####-####'),
            'balance_opening' => $this->faker->randomFloat(2, 0, 50000),
            'status' => AccountStatus::Active,
        ];
    }
}
