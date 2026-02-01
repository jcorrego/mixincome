<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $code
 * @property-read string $name
 * @property-read string $symbol
 * @property-read int $decimal_places
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, FxRate> $sourceFxRates
 * @property-read Collection<int, FxRate> $targetFxRates
 */
final class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'code' => 'string',
            'name' => 'string',
            'symbol' => 'string',
            'decimal_places' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * FX rates where this currency is the source.
     *
     * @return HasMany<FxRate, $this>
     */
    public function sourceFxRates(): HasMany
    {
        return $this->hasMany(FxRate::class, 'from_currency_id');
    }

    /**
     * FX rates where this currency is the target.
     *
     * @return HasMany<FxRate, $this>
     */
    public function targetFxRates(): HasMany
    {
        return $this->hasMany(FxRate::class, 'to_currency_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
