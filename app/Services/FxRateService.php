<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FxRateException;
use App\Models\Currency;
use App\Models\FxRate;
use Illuminate\Support\Carbon;

final class FxRateService
{
    public function __construct(
        private readonly EcbApiService $ecbApiService,
    ) {}

    /**
     * Find a rate for a currency pair on a specific date.
     * Falls back to most recent rate if exact date not available.
     * Returns identity rate (1.0) for same currency.
     */
    public function findRate(string $fromCode, string $toCode, Carbon $date): ?FxRate
    {
        if ($fromCode === $toCode) {
            return $this->createIdentityRate($fromCode, $date);
        }

        $fromCurrency = Currency::where('code', $fromCode)->first();
        $toCurrency = Currency::where('code', $toCode)->first();

        if ($fromCurrency === null || $toCurrency === null) {
            return null;
        }

        // Try exact date first
        $rate = FxRate::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->whereDate('date', $date)
            ->first();

        if ($rate !== null) {
            return $rate;
        }

        // Fall back to most recent rate before the date
        return FxRate::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->whereDate('date', '<=', $date)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Fetch a rate from API if not in local database.
     *
     * @throws FxRateException
     */
    public function fetchRate(string $fromCode, string $toCode, Carbon $date): FxRate
    {
        if ($fromCode === $toCode) {
            return $this->createIdentityRate($fromCode, $date);
        }

        // Check if already cached locally
        $existingRate = $this->findRate($fromCode, $toCode, $date);
        if ($existingRate !== null && $existingRate->date->toDateString() === $date->toDateString()) {
            return $existingRate;
        }

        // Fetch from API
        $apiResult = $this->ecbApiService->getRate($fromCode, $toCode, $date);

        $fromCurrency = Currency::where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::where('code', $toCode)->firstOrFail();

        return FxRate::create([
            'from_currency_id' => $fromCurrency->id,
            'to_currency_id' => $toCurrency->id,
            'date' => $apiResult['date'],
            'rate' => number_format($apiResult['rate'], 8, '.', ''),
            'source' => 'ecb',
            'is_replicated' => false,
            'replicated_from_date' => null,
        ]);
    }

    /**
     * Replicate a rate from a previous date (for weekends/holidays).
     *
     * @throws FxRateException
     */
    public function replicateRate(string $fromCode, string $toCode, Carbon $targetDate): FxRate
    {
        $fromCurrency = Currency::where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::where('code', $toCode)->firstOrFail();

        // Find the most recent rate before target date
        $sourceRate = FxRate::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->where('date', '<', $targetDate->toDateString())
            ->orderBy('date', 'desc')
            ->first();

        if ($sourceRate === null) {
            throw new FxRateException("No source rate available to replicate for {$fromCode}/{$toCode}");
        }

        return FxRate::create([
            'from_currency_id' => $fromCurrency->id,
            'to_currency_id' => $toCurrency->id,
            'date' => $targetDate->toDateString(),
            'rate' => $sourceRate->rate,
            'source' => $sourceRate->source,
            'is_replicated' => true,
            'replicated_from_date' => $sourceRate->date->toDateString(),
        ]);
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @throws FxRateException
     */
    public function convert(float $amount, string $fromCode, string $toCode, Carbon $date): float
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $rate = $this->findRate($fromCode, $toCode, $date);

        if ($rate === null) {
            throw new FxRateException("No rate available for {$fromCode}/{$toCode} on {$date->toDateString()}");
        }

        $toCurrency = Currency::where('code', $toCode)->firstOrFail();
        $result = $amount * (float) $rate->rate;

        return round($result, $toCurrency->decimal_places);
    }

    /**
     * Create a virtual identity rate for same-currency conversions.
     */
    private function createIdentityRate(string $currencyCode, Carbon $date): FxRate
    {
        $currency = Currency::where('code', $currencyCode)->first();

        $rate = new FxRate;
        $rate->from_currency_id = $currency?->id ?? 0;
        $rate->to_currency_id = $currency?->id ?? 0;
        $rate->date = $date;
        $rate->rate = '1.00000000';
        $rate->source = 'identity';
        $rate->is_replicated = false;
        $rate->replicated_from_date = null;

        return $rate;
    }
}
