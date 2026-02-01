<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\FxRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $from_currency_id
 * @property-read int $to_currency_id
 * @property-read CarbonInterface $date
 * @property-read string $rate
 * @property-read string $source
 * @property-read bool $is_replicated
 * @property-read CarbonInterface|null $replicated_from_date
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Currency $fromCurrency
 * @property-read Currency $toCurrency
 */
final class FxRate extends Model
{
    /** @use HasFactory<FxRateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'date',
        'rate',
        'source',
        'is_replicated',
        'replicated_from_date',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'from_currency_id' => 'integer',
            'to_currency_id' => 'integer',
            'date' => 'date',
            'rate' => 'string',
            'source' => 'string',
            'is_replicated' => 'boolean',
            'replicated_from_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The source currency for this rate.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * The target currency for this rate.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }
}
