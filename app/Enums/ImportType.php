<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportType: string
{
    case CSV = 'CSV';
    case QIF = 'QIF';
    case PDF = 'PDF';
    case YNABSync = 'YNABSync';
    case MercuryAPI = 'MercuryAPI';
    case SantanderCSV = 'SantanderCSV';
    case BancolombiaSFTP = 'BancolombiaSFTP';

    /**
     * Get human-readable label for the import type.
     */
    public function label(): string
    {
        return match ($this) {
            self::CSV => 'CSV File',
            self::QIF => 'QIF File (Quicken)',
            self::PDF => 'PDF Statement',
            self::YNABSync => 'YNAB Sync',
            self::MercuryAPI => 'Mercury Bank API',
            self::SantanderCSV => 'Santander CSV',
            self::BancolombiaSFTP => 'Bancolombia SFTP',
        };
    }

    /**
     * Check if this import type requires a file upload.
     */
    public function requiresFile(): bool
    {
        return match ($this) {
            self::CSV, self::QIF, self::PDF, self::SantanderCSV => true,
            self::YNABSync, self::MercuryAPI, self::BancolombiaSFTP => false,
        };
    }

    /**
     * Get accepted file extensions for this import type.
     *
     * @return array<string>
     */
    public function acceptedExtensions(): array
    {
        return match ($this) {
            self::CSV, self::SantanderCSV => ['csv'],
            self::QIF => ['qif'],
            self::PDF => ['pdf'],
            self::YNABSync, self::MercuryAPI, self::BancolombiaSFTP => [],
        };
    }

    /**
     * Get icon for import type display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::CSV, self::SantanderCSV => 'table-cells',
            self::QIF => 'document-text',
            self::PDF => 'document',
            self::YNABSync => 'cloud',
            self::MercuryAPI => 'bolt',
            self::BancolombiaSFTP => 'server',
        };
    }

    /**
     * Get all import types as array for select options.
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