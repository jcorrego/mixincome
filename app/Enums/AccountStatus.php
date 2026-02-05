<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Closed = 'Closed';

    /**
     * Get human-readable label for the account status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Closed => 'Closed',
        };
    }

    /**
     * Get CSS color class for status display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'yellow',
            self::Closed => 'red',
        };
    }

    /**
     * Check if account can have new transactions.
     */
    public function canCreateTransactions(): bool
    {
        return match ($this) {
            self::Active => true,
            self::Inactive, self::Closed => false,
        };
    }

    /**
     * Get all statuses as array for select options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->toArray();
    }
}