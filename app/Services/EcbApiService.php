<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FxRateException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class EcbApiService
{
    private const string BASE_URL = 'https://data-api.ecb.europa.eu/service/data/EXR';

    private const array SUPPORTED_CURRENCIES = ['USD', 'EUR', 'COP'];

    private const int CACHE_TTL_SECONDS = 86400; // 24 hours

    private const int RETRY_ATTEMPTS = 3;

    /**
     * Get exchange rate from ECB API.
     *
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    public function getRate(string $fromCurrency, string $toCurrency, Carbon $date): array
    {
        $this->validateCurrencies($fromCurrency, $toCurrency);

        $cacheKey = "ecb_rate_{$fromCurrency}_{$toCurrency}_{$date->toDateString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($fromCurrency, $toCurrency, $date): array {
            return $this->fetchFromApi($fromCurrency, $toCurrency, $date);
        });
    }

    /**
     * Validate that currencies are supported.
     *
     * @throws FxRateException
     */
    private function validateCurrencies(string $fromCurrency, string $toCurrency): void
    {
        if (! in_array($fromCurrency, self::SUPPORTED_CURRENCIES, true) ||
            ! in_array($toCurrency, self::SUPPORTED_CURRENCIES, true)) {
            throw new FxRateException('Unsupported currency pair');
        }
    }

    /**
     * Fetch rate from ECB API.
     *
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    private function fetchFromApi(string $fromCurrency, string $toCurrency, Carbon $date): array
    {
        // ECB publishes EUR-based rates, so we need to calculate cross rates
        $url = $this->buildUrl($fromCurrency, $date);

        try {
            $response = Http::retry(self::RETRY_ATTEMPTS, 1000, throw: false)
                ->timeout(30)
                ->get($url);

            if ($response->status() === 404) {
                throw new FxRateException('No rate available for the specified date');
            }

            if ($response->status() === 429) {
                throw new FxRateException('ECB API rate limit exceeded');
            }

            if (! $response->successful()) {
                throw new FxRateException('ECB API error: HTTP '.$response->status());
            }

            return $this->parseResponse($response->body(), $date);

        } catch (ConnectionException $e) {
            throw new FxRateException('ECB API connection failed: '.$e->getMessage());
        } catch (RequestException $e) {
            throw new FxRateException('ECB API error: '.$e->getMessage());
        }
    }

    /**
     * Build the SDMX URL for ECB API.
     */
    private function buildUrl(string $currency, Carbon $date): string
    {
        $formattedDate = $date->toDateString();

        // ECB SDMX 2.1 API endpoint for daily exchange rates
        return self::BASE_URL."/D.{$currency}.EUR.SP00.A?startPeriod={$formattedDate}&endPeriod={$formattedDate}&format=genericdata";
    }

    /**
     * Parse SDMX XML response.
     *
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    private function parseResponse(string $xmlContent, Carbon $date): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new FxRateException('Failed to parse ECB response: invalid XML');
        }

        // Register namespaces for SDMX parsing
        $xml->registerXPathNamespace('generic', 'http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic');
        $xml->registerXPathNamespace('message', 'http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message');

        $observations = $xml->xpath('//generic:Obs');

        if ($observations === false || count($observations) === 0) {
            throw new FxRateException('No rate found in ECB response');
        }

        $obs = $observations[0];
        $value = (string) $obs->xpath('generic:ObsValue/@value')[0] ?? null;

        if ($value === null || $value === '') {
            throw new FxRateException('No rate value in ECB response');
        }

        return [
            'rate' => (float) $value,
            'date' => $date->toDateString(),
        ];
    }
}
