<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FxRateException;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

final class ExchangeRateApiService
{
    private const string BASE_URL = 'https://v6.exchangerate-api.com/v6';

    private const array SUPPORTED_CURRENCIES = ['USD', 'EUR', 'COP'];

    private const int CACHE_TTL_SECONDS = 86400; // 24 hours

    private const int RETRY_ATTEMPTS = 3;

    /**
     * Get exchange rate from ExchangeRate-API.
     *
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    public function getRate(string $fromCurrency, string $toCurrency, CarbonInterface $date): array
    {
        $this->validateCurrencies($fromCurrency, $toCurrency);

        $cacheKey = "exchangerate_api_{$fromCurrency}_{$toCurrency}_{$date->toDateString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn (): array => $this->fetchFromApi($fromCurrency, $toCurrency, $date));
    }

    /**
     * Validate that currencies are supported.
     *
     * @throws FxRateException
     */
    private function validateCurrencies(string $fromCurrency, string $toCurrency): void
    {
        throw_if(! in_array($fromCurrency, self::SUPPORTED_CURRENCIES, true) ||
            ! in_array($toCurrency, self::SUPPORTED_CURRENCIES, true), FxRateException::class, 'Unsupported currency pair');
    }

    /**
     * Fetch rate from ExchangeRate-API.
     *
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    private function fetchFromApi(string $fromCurrency, string $toCurrency, CarbonInterface $date): array
    {
        $apiKey = config('services.exchangerate_api.key');

        throw_if(empty($apiKey), FxRateException::class, 'ExchangeRate-API key not configured');

        /** @var string $apiKey */
        $url = $this->buildUrl($apiKey, $fromCurrency, $toCurrency, $date);

        try {
            $response = Http::retry(self::RETRY_ATTEMPTS, 1000, throw: false)
                ->timeout(30)
                ->get($url);

            throw_if(! $response->successful(), FxRateException::class, 'ExchangeRate-API error: HTTP '.$response->status());

            $data = $response->json();
            throw_if($data === null, FxRateException::class, 'ExchangeRate-API returned invalid JSON');

            /** @var array<string, mixed> $data */
            return $this->parseResponse($data, $date, $toCurrency);

        } catch (ConnectionException $e) {
            throw new FxRateException('ExchangeRate-API connection failed: '.$e->getMessage());
        } catch (RequestException $e) {
            throw new FxRateException('ExchangeRate-API error: '.$e->getMessage());
        }
    }

    /**
     * Build the URL for ExchangeRate-API.
     * Uses /history endpoint for past dates, /pair endpoint for today.
     */
    private function buildUrl(string $apiKey, string $fromCurrency, string $toCurrency, CarbonInterface $date): string
    {
        $today = Date::today();
        if ($date->isSameDay($today)) {
            return self::BASE_URL."/{$apiKey}/pair/{$fromCurrency}/{$toCurrency}";
        }

        return self::BASE_URL."/{$apiKey}/history/{$fromCurrency}/{$date->toDateString()}/{$toCurrency}";
    }

    /**
     * Parse ExchangeRate-API response.
     * Handles both /pair endpoint (current rates) and /history endpoint (historical rates).
     *
     * @param  array<string, mixed>  $data
     * @return array{rate: float, date: string}
     *
     * @throws FxRateException
     */
    private function parseResponse(array $data, CarbonInterface $date, string $toCurrency = ''): array
    {
        /** @var mixed $result */
        $result = $data['result'] ?? null;
        if ($result !== 'success') {
            $error = $data['error-type'] ?? 'Unknown error';
            /** @phpstan-ignore-next-line cast.string */
            $errorMsg = (string) $error;
            throw new FxRateException('ExchangeRate-API error: '.$errorMsg);
        }

        // For history endpoint, the rate is in conversion_rates object; for /pair endpoint, it's conversion_rate
        $conversionRateValue = $data['conversion_rate'] ?? null;
        if ($conversionRateValue === null && isset($data['conversion_rates']) && is_array($data['conversion_rates'])) {
            $conversionRateValue = $data['conversion_rates'][$toCurrency] ?? null;
        }
        throw_if(
            $conversionRateValue === null,
            FxRateException::class,
            'No rate found in ExchangeRate-API response'
        );

        /** @var float|string|int $conversionRate */
        $conversionRate = $conversionRateValue;

        return [
            'rate' => (float) $conversionRate,
            'date' => $date->toDateString(),
        ];
    }
}
