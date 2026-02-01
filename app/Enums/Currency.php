<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Supported currencies in the MixIncome application.
 */
enum Currency: string
{
    case Usd = 'USD';
    case Eur = 'EUR';
    case Cop = 'COP';

    /**
     * Get the number of decimal places for this currency.
     */
    public function decimals(): int
    {
        return match ($this) {
            self::Usd, self::Eur => 2,
            self::Cop => 0,
        };
    }

    /**
     * Get the currency symbol.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::Usd, self::Cop => '$',
            self::Eur => '€',
        };
    }

    /**
     * Get the human-readable name.
     */
    public function name(): string
    {
        return match ($this) {
            self::Usd => 'United States Dollar',
            self::Eur => 'Euro',
            self::Cop => 'Colombian Peso',
        };
    }
}
