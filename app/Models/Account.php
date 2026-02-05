<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_id',
        'name',
        'account_type',
        'currency_id',
        'account_number',
        'balance_opening',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'account_type' => AccountType::class,
        'status' => AccountStatus::class,
        'balance_opening' => 'decimal:2',
        'account_number' => 'encrypted',
    ];

    /**
     * Get the entity that owns this account.
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Get the currency for this account.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get all transactions for this account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all addresses for this account (polymorphic).
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the account's primary address.
     */
    public function address(): ?Address
    {
        return $this->addresses()->first();
    }

    /**
     * Check if the account can create new transactions.
     */
    public function canCreateTransactions(): bool
    {
        return $this->status->canCreateTransactions();
    }

    /**
     * Get formatted account number (masked for security).
     */
    public function getFormattedAccountNumber(): string
    {
        if (! $this->account_number) {
            return 'N/A';
        }

        $number = $this->account_number;
        if (strlen($number) <= 4) {
            return $number;
        }

        return '****' . substr($number, -4);
    }

    /**
     * Get the account type label.
     */
    public function getTypeLabel(): string
    {
        return $this->account_type->label();
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColor(): string
    {
        return $this->status->color();
    }
}
