<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportStatus: string
{
    case Processing = 'Processing';
    case Imported = 'Imported';
    case Failed = 'Failed';
    case Duplicate = 'Duplicate';
    case Review = 'Review';

    /**
     * Get human-readable label for the import status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Processing => 'Processing',
            self::Imported => 'Imported',
            self::Failed => 'Failed',
            self::Duplicate => 'Duplicate',
            self::Review => 'Needs Review',
        };
    }

    /**
     * Get CSS color class for status display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Processing => 'blue',
            self::Imported => 'green',
            self::Failed => 'red',
            self::Duplicate => 'yellow',
            self::Review => 'orange',
        };
    }

    /**
     * Get icon for status display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Processing => 'clock',
            self::Imported => 'check-circle',
            self::Failed => 'x-circle',
            self::Duplicate => 'exclamation-triangle',
            self::Review => 'eye',
        };
    }

    /**
     * Check if this status indicates the import is complete.
     */
    public function isComplete(): bool
    {
        return match ($this) {
            self::Imported, self::Failed, self::Duplicate => true,
            self::Processing, self::Review => false,
        };
    }

    /**
     * Check if this status indicates success.
     */
    public function isSuccess(): bool
    {
        return $this === self::Imported;
    }

    /**
     * Check if this status indicates an error.
     */
    public function isError(): bool
    {
        return match ($this) {
            self::Failed, self::Duplicate => true,
            self::Processing, self::Imported, self::Review => false,
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