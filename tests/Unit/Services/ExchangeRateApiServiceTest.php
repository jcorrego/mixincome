<?php

declare(strict_types=1);

use App\Exceptions\FxRateException;
use App\Services\ExchangeRateApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->service = resolve(ExchangeRateApiService::class);

    $this->validResponse = [
        'result' => 'success',
        'conversion_rate' => 0.93,
        'time_last_update_utc' => 'Sat, 14 Jun 2024 00:00:01 +0000',
    ];

    config(['services.exchangerate_api.key' => 'test-api-key']);
});

test('getRate fetches rate successfully from API', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $result = $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['rate', 'date'])
        ->and($result['rate'])->toBe(0.93)
        ->and($result['date'])->toBeString();
});

test('getRate constructs correct API URL for historical date', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    Http::assertSent(function ($request): bool {
        $url = $request->url();

        return str_contains($url, 'v6.exchangerate-api.com/v6/test-api-key/history/COP/2024-06-14/EUR');
    });
});

test('getRate constructs correct API URL for current date', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::today());

    Http::assertSent(function ($request): bool {
        $url = $request->url();

        return str_contains($url, 'v6.exchangerate-api.com/v6/test-api-key/pair/COP/EUR');
    });
});

test('getRate parses history endpoint response correctly', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response([
            'result' => 'success',
            'conversion_rates' => [
                'EUR' => 0.93,
            ],
        ], 200),
    ]);

    $result = $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    expect($result['rate'])->toBe(0.93);
});

test('getRate caches response for same request', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    Http::assertSentCount(1);
});

test('getRate throws exception when API key not configured', function (): void {
    config(['services.exchangerate_api.key' => null]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API key not configured');

test('getRate throws exception for unsupported currencies', function (): void {
    $this->service->getRate('XXX', 'YYY', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'Unsupported currency');

test('getRate throws exception when HTTP request fails', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response('Server Error', 500),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API error: HTTP 500');

test('getRate throws exception on network timeout', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => fn () => throw new ConnectionException('Timeout'),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API connection failed');

test('getRate throws exception on request exception', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => function (): void {
            $response = new Response(
                new GuzzleHttp\Psr7\Response(500)
            );

            throw new RequestException($response);
        },
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API error');

test('getRate retries on temporary failure', function (): void {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount < 3) {
            return Http::response('Server Error', 500);
        }

        return Http::response($this->validResponse, 200);
    });

    $result = $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    expect($callCount)->toBe(3)
        ->and($result['rate'])->toBe(0.93);
});

test('getRate throws exception when response missing conversion_rate', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response([
            'result' => 'success',
        ], 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'No rate found in ExchangeRate-API response');

test('getRate throws exception when response is not JSON', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response('not json', 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API returned invalid JSON');

test('getRate supports USD to EUR pair', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $result = $this->service->getRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($result['rate'])->toBe(0.93);
});

test('getRate supports COP to USD pair', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response([
            'result' => 'success',
            'conversion_rate' => 4000.12,
        ], 200),
    ]);

    $result = $this->service->getRate('COP', 'USD', Date::parse('2024-06-14'));

    expect($result['rate'])->toBe(4000.12);
});

test('getRate validates from currency is supported', function (): void {
    $this->service->getRate('GBP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'Unsupported currency pair');

test('getRate validates to currency is supported', function (): void {
    $this->service->getRate('COP', 'GBP', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'Unsupported currency pair');

test('getRate returns same rate for same currency pair on same day', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $result1 = $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
    $result2 = $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));

    expect($result1['rate'])->toBe($result2['rate']);
    Http::assertSentCount(1); // Cached
});

test('getRate fetches new rate for different date', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-15'));

    Http::assertSentCount(2); // Not cached
});

test('getRate formats date correctly in cache key', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response($this->validResponse, 200),
    ]);

    $date = Date::parse('2024-06-14');
    $this->service->getRate('COP', 'EUR', $date);
    $this->service->getRate('COP', 'EUR', $date);

    Http::assertSentCount(1); // Same date, should be cached
});

test('getRate throws exception when result is not success', function (): void {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response([
            'result' => 'error',
            'error-type' => 'unsupported-code',
        ], 200),
    ]);

    $this->service->getRate('COP', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ExchangeRate-API error: unsupported-code');
