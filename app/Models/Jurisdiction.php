<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\JurisdictionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $iso_code
 * @property-read string $timezone
 * @property-read string $default_currency
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Jurisdiction extends Model
{
    /** @use HasFactory<JurisdictionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'iso_code',
        'timezone',
        'default_currency',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'iso_code' => 'string',
            'timezone' => 'string',
            'default_currency' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
