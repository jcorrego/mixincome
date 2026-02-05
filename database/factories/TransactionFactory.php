<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $currency = $this->faker->randomElement([Currency::Usd, Currency::Eur, Currency::Cop]);
        $amount = $this->faker->randomFloat(2, -5000, 5000);
        
        $amountField = match ($currency) {
            Currency::Usd => 'amount_usd',
            Currency::Eur => 'amount_eur',  
            Currency::Cop => 'amount_cop',
        };

        return [
            'account_id' => Account::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->sentence,
            'original_currency' => $currency->value,
            $amountField => $amount,
        ];
    }
}
