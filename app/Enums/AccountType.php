<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'Checking';
    case Savings = 'Savings';
    case CreditCard = 'CreditCard';
    case Investment = 'Investment';
    case Crypto = 'Crypto';
    case Cash = 'Cash';
    case Loan = 'Loan';
    case LineOfCredit = 'LineOfCredit';

    /**
     * Get human-readable label for the account type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Checking Account',
            self::Savings => 'Savings Account',
            self::CreditCard => 'Credit Card',
            self::Investment => 'Investment Account',
            self::Crypto => 'Cryptocurrency Wallet',
            self::Cash => 'Cash Account',
            self::Loan => 'Loan Account',
            self::LineOfCredit => 'Line of Credit',
        };
    }

    /**
     * Get all account types as array for select options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }
}