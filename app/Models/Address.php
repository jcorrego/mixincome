<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic address for users, entities, accounts, and assets.
 *
 * @property-read int $id
 * @property int|null $addressable_id
 * @property string|null $addressable_type
 * @property int $user_id
 * @property string $street
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Model $addressable
 * @property-read User $user
 */
final class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'user_id',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    public function casts(): array
    {
        return [
            'addressable_id' => 'int',
            'user_id' => 'int',
            'street' => 'string',
            'city' => 'string',
            'state' => 'string',
            'postal_code' => 'string',
            'country' => 'string',
        ];
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
