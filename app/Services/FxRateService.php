<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FxRateException;
use App\Models\Currency;
use App\Models\FxRate;
use Carbon\CarbonInterface;

final readonly class FxRateService
{
    public function __construct(
        private EcbApiService $ecbApiService,
    ) {}

    /**
     * Find a rate for a currency pair on a specific date.
     * Falls back to most recent rate if exact date not available.
     * Returns identity rate (1.0) for same currency.
     */
    public function findRate(string $fromCode, string $toCode, CarbonInterface $date): ?FxRate
    {
        if ($fromCode === $toCode) {
            return $this->createIdentityRate($fromCode, $date);
        }

        $fromCurrency = Currency::query()->where('code', $fromCode)->first();
        $toCurrency = Currency::query()->where('code', $toCode)->first();

        if ($fromCurrency === null || $toCurrency === null) {
            return null;
        }

        // Try exact date first
        $rate = FxRate::query()->where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->whereDate('date', $date)
            ->first();

        if ($rate !== null) {
            return $rate;
        }

        // Fall back to most recent rate before the date
        return FxRate::query()->where('from_currency_id', $fromCurrency->id)
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
    public function fetchRate(string $fromCode, string $toCode, CarbonInterface $date): FxRate
    {
        if ($fromCode === $toCode) {
            return $this->createIdentityRate($fromCode, $date);
        }

        // Check if already cached locally
        $existingRate = $this->findRate($fromCode, $toCode, $date);
        if ($existingRate instanceof FxRate && $existingRate->date->toDateString() === $date->toDateString()) {
            return $existingRate;
        }

        // Fetch from API
        $apiResult = $this->ecbApiService->getRate($fromCode, $toCode, $date);

        $fromCurrency = Currency::query()->where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::query()->where('code', $toCode)->firstOrFail();

        return FxRate::query()->create([
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
    public function replicateRate(string $fromCode, string $toCode, CarbonInterface $targetDate): FxRate
    {
        $fromCurrency = Currency::query()->where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::query()->where('code', $toCode)->firstOrFail();

        // Find the most recent rate before target date
        $sourceRate = FxRate::query()->where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->where('date', '<', $targetDate->toDateString())
            ->orderBy('date', 'desc')
            ->first();

        throw_if($sourceRate === null, FxRateException::class, "No source rate available to replicate for {$fromCode}/{$toCode}");

        return FxRate::query()->create([
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
    public function convert(float $amount, string $fromCode, string $toCode, CarbonInterface $date): float
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $rate = $this->findRate($fromCode, $toCode, $date);

        if (! $rate instanceof FxRate) {
            throw new FxRateException("No rate available for {$fromCode}/{$toCode} on {$date->toDateString()}");
        }

        $toCurrency = Currency::query()->where('code', $toCode)->firstOrFail();
        $result = $amount * (float) $rate->rate;

        return round($result, $toCurrency->decimal_places);
    }

    /**
     * Create a virtual identity rate for same-currency conversions.
     */
    private function createIdentityRate(string $currencyCode, CarbonInterface $date): FxRate
    {
        $currency = Currency::query()->where('code', $currencyCode)->first();
        $currencyId = $currency !== null ? $currency->id : 0;

        return new FxRate([
            'from_currency_id' => $currencyId,
            'to_currency_id' => $currencyId,
            'date' => $date,
            'rate' => '1.00000000',
            'source' => 'identity',
            'is_replicated' => false,
            'replicated_from_date' => null,
        ]);
    }
}
