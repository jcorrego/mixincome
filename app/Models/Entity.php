<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntityType;
use Carbon\Carbon;
use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Legal entity (LLC, Corp, Partnership, etc.) belonging to a user profile.
 *
 * @property-read int $id
 * @property int $user_profile_id
 * @property string $name
 * @property EntityType $entity_type
 * @property string $tax_id
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read UserProfile $userProfile
 * @property-read Address|null $address
 */
final class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'Active',
    ];

    protected $fillable = [
        'user_profile_id',
        'name',
        'entity_type',
        'tax_id',
        'status',
    ];

    public function casts(): array
    {
        return [
            'user_profile_id' => 'int',
            'entity_type' => EntityType::class,
            'status' => 'string',
        ];
    }

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
