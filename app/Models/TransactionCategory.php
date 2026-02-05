<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionCategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TransactionCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'category_type',
        'description',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'category_type' => TransactionCategoryType::class,
        'is_system' => 'boolean',
    ];

    /**
     * Get all transactions for this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    /**
     * Get the category type label.
     */
    public function getTypeLabel(): string
    {
        return $this->category_type->label();
    }

    /**
     * Get the category type color for UI display.
     */
    public function getTypeColor(): string
    {
        return $this->category_type->color();
    }

    /**
     * Get the category type icon.
     */
    public function getTypeIcon(): string
    {
        return $this->category_type->icon();
    }

    /**
     * Check if this is a system category.
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Check if this category can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // System categories cannot be deleted
        if ($this->is_system) {
            return false;
        }

        // Categories with transactions cannot be deleted
        return $this->transactions()->count() === 0;
    }

    /**
     * Get the transaction count for this category.
     */
    public function getTransactionCount(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Scope to filter by category type.
     */
    public function scopeOfType(Builder $query, TransactionCategoryType $type): Builder
    {
        return $query->where('category_type', $type);
    }

    /**
     * Scope to filter system categories.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to filter custom categories.
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to search categories by name or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
