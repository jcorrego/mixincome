<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Independent address resource that can be reused by users, entities, accounts, and assets.
 *
 * @property-read int $id
 * @property int $user_id
 * @property string $street
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read string $display_label
 * @property-read Collection<int, UserProfile> $userProfiles
 * @property-read Collection<int, Entity> $entities
 */
final class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected $fillable = [
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
            'id' => 'int',
            'user_id' => 'int',
            'street' => 'string',
            'city' => 'string',
            'state' => 'string',
            'postal_code' => 'string',
            'country' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getDisplayLabelAttribute(): string
    {
        return "{$this->street}, {$this->city} ({$this->country})";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }
}
