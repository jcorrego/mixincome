<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionCategoryType: string
{
    case Income = 'Income';
    case Expense = 'Expense';
    case Transfer = 'Transfer';
    case Tax = 'Tax';
    case Other = 'Other';

    /**
     * Get human-readable label for the category type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
            self::Transfer => 'Transfer',
            self::Tax => 'Tax',
            self::Other => 'Other',
        };
    }

    /**
     * Get CSS color class for category type display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Income => 'green',
            self::Expense => 'red',
            self::Transfer => 'blue',
            self::Tax => 'purple',
            self::Other => 'gray',
        };
    }

    /**
     * Get icon for category type display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Income => 'plus-circle',
            self::Expense => 'minus-circle',
            self::Transfer => 'arrow-right',
            self::Tax => 'calculator',
            self::Other => 'question-mark-circle',
        };
    }

    /**
     * Get all category types as array for select options.
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