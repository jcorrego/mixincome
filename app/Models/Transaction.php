<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

final class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'category_id',
        'import_id',
        'date',
        'description',
        'amount_usd',
        'amount_eur',
        'amount_cop',
        'original_currency',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'amount_usd' => 'decimal:2',
        'amount_eur' => 'decimal:2',
        'amount_cop' => 'decimal:0', // COP has no decimals
        'original_currency' => Currency::class,
    ];

    /**
     * Get the account this transaction belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category this transaction is assigned to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    /**
     * Get the import batch this transaction belongs to.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(TransactionImport::class, 'import_id');
    }

    /**
     * Get the original amount (in the original currency).
     */
    public function getOriginalAmount(): string
    {
        $amount = match ($this->original_currency) {
            Currency::Usd => $this->amount_usd,
            Currency::Eur => $this->amount_eur,
            Currency::Cop => $this->amount_cop,
        };

        if ($amount === null) {
            throw new InvalidArgumentException(
                "Original amount for currency {$this->original_currency->value} is null"
            );
        }

        return (string) $amount;
    }

    /**
     * Get the amount in a specific currency (returns null if not converted yet).
     */
    public function getAmountIn(Currency $currency): ?string
    {
        $amount = match ($currency) {
            Currency::Usd => $this->amount_usd,
            Currency::Eur => $this->amount_eur,
            Currency::Cop => $this->amount_cop,
        };

        return $amount !== null ? (string) $amount : null;
    }

    /**
     * Set the amount for a specific currency.
     */
    public function setAmountIn(Currency $currency, float|string|null $amount): void
    {
        $column = match ($currency) {
            Currency::Usd => 'amount_usd',
            Currency::Eur => 'amount_eur',
            Currency::Cop => 'amount_cop',
        };

        // For COP, round to no decimals
        if ($currency === Currency::Cop) {
            $amount = round((float) $amount);
        }

        $this->update([$column => $amount]);
    }

    /**
     * Check if the transaction has been converted to a specific currency.
     */
    public function hasAmountIn(Currency $currency): bool
    {
        return $this->getAmountIn($currency) !== null;
    }

    /**
     * Get all available converted amounts.
     *
     * @return array<string, string>
     */
    public function getAvailableAmounts(): array
    {
        $amounts = [];

        foreach (Currency::cases() as $currency) {
            $amount = $this->getAmountIn($currency);
            if ($amount !== null) {
                $amounts[$currency->value] = $amount;
            }
        }

        return $amounts;
    }

    /**
     * Get the category name or "Uncategorized" if no category.
     */
    public function getCategoryName(): string
    {
        return $this->category->name ?? 'Uncategorized';
    }

    /**
     * Get the category type or null if no category.
     */
    public function getCategoryType(): ?string
    {
        return $this->category?->category_type->value;
    }

    /**
     * Check if the transaction is categorized.
     */
    public function isCategorized(): bool
    {
        return $this->category_id !== null;
    }

    /**
     * Check if the transaction was imported.
     */
    public function wasImported(): bool
    {
        return $this->import_id !== null;
    }

    /**
     * Check if the amount is positive (income).
     */
    public function isIncome(): bool
    {
        return (float) $this->getOriginalAmount() > 0;
    }

    /**
     * Check if the amount is negative (expense).
     */
    public function isExpense(): bool
    {
        return (float) $this->getOriginalAmount() < 0;
    }

    /**
     * Get the absolute amount (always positive).
     */
    public function getAbsoluteAmount(): string
    {
        return (string) abs((float) $this->getOriginalAmount());
    }

    /**
     * Get formatted date string.
     */
    public function getFormattedDate(string $format = 'Y-m-d'): string
    {
        return $this->date->format($format);
    }

    /**
     * Scope to filter transactions by account.
     */
    public function scopeForAccount(Builder $query, Account $account): Builder
    {
        return $query->where('account_id', $account->id);
    }

    /**
     * Scope to filter transactions by category.
     */
    public function scopeInCategory(Builder $query, TransactionCategory $category): Builder
    {
        return $query->where('category_id', $category->id);
    }

    /**
     * Scope to filter uncategorized transactions.
     */
    public function scopeUncategorized(Builder $query): Builder
    {
        return $query->whereNull('category_id');
    }

    /**
     * Scope to filter transactions by date range.
     */
    public function scopeBetweenDates(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope to filter transactions by year.
     */
    public function scopeInYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope to filter income transactions (positive amounts).
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('amount_usd', '>', 0)
                ->orWhere('amount_eur', '>', 0)
                ->orWhere('amount_cop', '>', 0);
        });
    }

    /**
     * Scope to filter expense transactions (negative amounts).
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('amount_usd', '<', 0)
                ->orWhere('amount_eur', '<', 0)
                ->orWhere('amount_cop', '<', 0);
        });
    }

    /**
     * Scope to filter by original currency.
     */
    public function scopeInCurrency(Builder $query, Currency $currency): Builder
    {
        return $query->where('original_currency', $currency);
    }

    /**
     * Scope to order by date descending (newest first).
     */
    public function scopeNewest(Builder $query): Builder
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order by date ascending (oldest first).
     */
    public function scopeOldest(Builder $query): Builder
    {
        return $query->orderBy('date', 'asc')->orderBy('created_at', 'asc');
    }

    /**
     * Scope to search transactions by description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }
}
