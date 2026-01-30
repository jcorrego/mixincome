<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * User's tax profile for a specific jurisdiction.
 *
 * @property-read int $id
 * @property int $user_id
 * @property int $jurisdiction_id
 * @property string $tax_id
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Jurisdiction $jurisdiction
 * @property-read Collection<int, Entity> $entities
 * @property-read Address|null $address
 */
final class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'Active',
    ];

    protected $fillable = [
        'user_id',
        'jurisdiction_id',
        'tax_id',
        'status',
    ];

    public function casts(): array
    {
        return [
            'user_id' => 'int',
            'jurisdiction_id' => 'int',
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
