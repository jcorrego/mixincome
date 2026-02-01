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
        private ExchangeRateApiService $exchangeRateApiService,
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

        // Fetch from API (choose appropriate service based on currency pair)
        if ($this->isCopPair($fromCode, $toCode)) {
            $apiResult = $this->exchangeRateApiService->getRate($fromCode, $toCode, $date);
            $source = 'exchangerate-api';
        } else {
            $apiResult = $this->ecbApiService->getRate($fromCode, $toCode, $date);
            $source = 'ecb';
        }

        $fromCurrency = Currency::query()->where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::query()->where('code', $toCode)->firstOrFail();

        return FxRate::query()->firstOrCreate(
            [
                'from_currency_id' => $fromCurrency->id,
                'to_currency_id' => $toCurrency->id,
                'date' => $apiResult['date'],
            ],
            [
                'rate' => number_format($apiResult['rate'], 8, '.', ''),
                'source' => $source,
                'is_replicated' => false,
                'replicated_from_date' => null,
            ]
        );
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
     * Manually fetch a new rate for a specific date (admin operation).
     * Throws exception if rate already exists or if the appropriate API (ECB or ExchangeRate-API) has no data for the requested date.
     */
    public function fetchRateManual(Currency $from, Currency $to, CarbonInterface $date): FxRate
    {
        // Check if rate already exists
        $existing = FxRate::query()
            ->where('from_currency_id', $from->id)
            ->where('to_currency_id', $to->id)
            ->whereDate('date', $date)
            ->first();

        throw_if($existing !== null, FxRateException::class, 'Rate already exists for this currency pair and date');

        // Try to fetch from appropriate API
        try {
            if ($this->isCopPair($from->code, $to->code)) {
                $apiResult = $this->exchangeRateApiService->getRate($from->code, $to->code, $date);
                $source = 'exchangerate-api';
            } else {
                $apiResult = $this->ecbApiService->getRate($from->code, $to->code, $date);
                $source = 'ecb';
            }
            $rate = $apiResult['rate'];
        } catch (FxRateException) {
            throw new FxRateException('API has no rate for this date. Rate would be replicated.');
        }

        // Create the rate
        return FxRate::query()->firstOrCreate(
            [
                'from_currency_id' => $from->id,
                'to_currency_id' => $to->id,
                'date' => $date,
            ],
            [
                'rate' => $rate,
                'source' => $source,
                'is_replicated' => false,
                'replicated_from_date' => null,
            ]
        );
    }

    /**
     * Re-fetch an existing rate from the appropriate API service (admin operation).
     * Updates the rate value and replication status if the API has new data.
     */
    public function refetchRate(FxRate $rate): FxRate
    {
        $fromCurrency = $rate->fromCurrency;
        $toCurrency = $rate->toCurrency;

        // Try to fetch fresh data from appropriate API
        try {
            if ($this->isCopPair($fromCurrency->code, $toCurrency->code)) {
                $apiResult = $this->exchangeRateApiService->getRate($fromCurrency->code, $toCurrency->code, $rate->date);
            } else {
                $apiResult = $this->ecbApiService->getRate($fromCurrency->code, $toCurrency->code, $rate->date);
            }
            $newRateValue = $apiResult['rate'];
        } catch (FxRateException) {
            throw new FxRateException('API has no rate for this date');
        }

        // Update the rate
        $rate->update([
            'rate' => number_format($newRateValue, 8, '.', ''),
            'is_replicated' => false,
            'replicated_from_date' => null,
        ]);

        // Always touch to update timestamp even if values didn't change
        $rate->touch();
        $rate->refresh();

        return $rate;
    }

    /**
     * Determine if currency pair involves COP.
     */
    private function isCopPair(string $fromCode, string $toCode): bool
    {
        return $fromCode === 'COP' || $toCode === 'COP';
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
