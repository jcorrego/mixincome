<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Currency;
use App\Exceptions\FxRateException;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class CurrencyConversionService
{
    public function __construct(
        private FxRateService $fxRateService
    ) {}

    /**
     * Convert a transaction to the target currency.
     * Uses lazy conversion - returns cached value if available.
     *
     * @throws FxRateException When exchange rate is not available
     */
    public function convert(Transaction $transaction, Currency $targetCurrency): string
    {
        // If already converted, return cached value
        $existingAmount = $transaction->getAmountIn($targetCurrency);
        if ($existingAmount !== null) {
            return $existingAmount;
        }

        // If target currency is the original, return original amount and cache it
        if ($transaction->original_currency === $targetCurrency) {
            $originalAmount = $transaction->getOriginalAmount();
            $this->updateTransactionAmount($transaction, $targetCurrency, $originalAmount);
            return $originalAmount;
        }

        // Get exchange rate for the transaction date
        $fxRate = $this->fxRateService->findRate(
            fromCode: $transaction->original_currency->value,
            toCode: $targetCurrency->value,
            date: $transaction->date
        );

        // If not found, fetch from API
        if ($fxRate === null) {
            $fxRate = $this->fxRateService->fetchRate(
                fromCode: $transaction->original_currency->value,
                toCode: $targetCurrency->value,
                date: $transaction->date
            );
        }

        $rate = $fxRate->rate;

        // Calculate converted amount
        $originalAmount = (float) $transaction->getOriginalAmount();
        $convertedAmount = $originalAmount * $rate;

        // Handle COP precision (no decimals)
        if ($targetCurrency === Currency::Cop) {
            $convertedAmount = round($convertedAmount);
        }

        // Cache the converted amount
        $this->updateTransactionAmount($transaction, $targetCurrency, (string) $convertedAmount);

        return (string) $convertedAmount;
    }

    /**
     * Convert multiple transactions to the target currency.
     * Optimized for batch processing.
     *
     * @param Collection<int, Transaction> $transactions
     * @return Collection<int, string> Array of converted amounts
     */
    public function convertBatch(Collection $transactions, Currency $targetCurrency): Collection
    {
        return $transactions->map(function (Transaction $transaction) use ($targetCurrency) {
            try {
                return $this->convert($transaction, $targetCurrency);
            } catch (FxRateException $e) {
                // Log error but continue with other transactions
                logger()->warning('Failed to convert transaction', [
                    'transaction_id' => $transaction->id,
                    'target_currency' => $targetCurrency->value,
                    'error' => $e->getMessage(),
                ]);
                
                // Return null or original amount as fallback
                return $transaction->original_currency === $targetCurrency 
                    ? $transaction->getOriginalAmount() 
                    : null;
            }
        });
    }

    /**
     * Get all transactions converted to a specific currency for a date range.
     * Useful for reports that need all amounts in the same currency.
     *
     * @param Collection<int, Transaction> $transactions
     */
    public function convertAllToReportingCurrency(
        Collection $transactions,
        Currency $reportingCurrency
    ): Collection {
        return $transactions->map(function (Transaction $transaction) use ($reportingCurrency) {
            $convertedAmount = $this->convert($transaction, $reportingCurrency);
            
            return [
                'transaction' => $transaction,
                'amount' => $convertedAmount,
                'currency' => $reportingCurrency->value,
            ];
        });
    }

    /**
     * Check if a transaction needs conversion for the given currency.
     */
    public function needsConversion(Transaction $transaction, Currency $targetCurrency): bool
    {
        // Already has amount in target currency
        if ($transaction->hasAmountIn($targetCurrency)) {
            return false;
        }

        // Original currency is target currency (should be cached but isn't)
        if ($transaction->original_currency === $targetCurrency) {
            return false; // Will be handled by convert() method
        }

        return true;
    }

    /**
     * Get conversion statistics for a collection of transactions.
     *
     * @param Collection<int, Transaction> $transactions
     * @return array<string, mixed>
     */
    public function getConversionStats(Collection $transactions, Currency $targetCurrency): array
    {
        $needConversion = 0;
        $alreadyConverted = 0;
        $sameAsOriginal = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->original_currency === $targetCurrency) {
                $sameAsOriginal++;
            } elseif ($transaction->hasAmountIn($targetCurrency)) {
                $alreadyConverted++;
            } else {
                $needConversion++;
            }
        }

        return [
            'total' => $transactions->count(),
            'need_conversion' => $needConversion,
            'already_converted' => $alreadyConverted,
            'same_as_original' => $sameAsOriginal,
            'conversion_rate' => $transactions->count() > 0 
                ? round(($alreadyConverted / $transactions->count()) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Update the transaction amount for a specific currency.
     */
    private function updateTransactionAmount(
        Transaction $transaction,
        Currency $currency,
        string $amount
    ): void {
        $transaction->setAmountIn($currency, $amount);
    }

    /**
     * Clear cached conversions for a transaction.
     * Useful when exchange rates are updated and you want to re-convert.
     */
    public function clearConversions(Transaction $transaction, ?Currency $currency = null): void
    {
        if ($currency) {
            // Clear specific currency
            $transaction->setAmountIn($currency, null);
        } else {
            // Clear all conversions except original
            foreach (Currency::cases() as $curr) {
                if ($curr !== $transaction->original_currency) {
                    $transaction->setAmountIn($curr, null);
                }
            }
        }
    }

    /**
     * Validate that a transaction has a valid original amount.
     */
    public function validateTransaction(Transaction $transaction): bool
    {
        try {
            $originalAmount = $transaction->getOriginalAmount();
            return is_numeric($originalAmount);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Get the effective exchange rate used for a transaction conversion.
     * Returns null if the transaction hasn't been converted to the target currency.
     */
    public function getEffectiveRate(Transaction $transaction, Currency $targetCurrency): ?float
    {
        // Same currency = rate of 1.0
        if ($transaction->original_currency === $targetCurrency) {
            return 1.0;
        }

        // Check if conversion exists
        $convertedAmount = $transaction->getAmountIn($targetCurrency);
        if ($convertedAmount === null) {
            return null;
        }

        $originalAmount = (float) $transaction->getOriginalAmount();
        if ($originalAmount === 0.0) {
            return null;
        }

        return (float) $convertedAmount / $originalAmount;
    }
}