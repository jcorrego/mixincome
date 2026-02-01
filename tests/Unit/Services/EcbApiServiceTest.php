<?php

declare(strict_types=1);

use App\Exceptions\FxRateException;
use App\Services\EcbApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->ecbApiService = resolve(EcbApiService::class);
    $this->validEcbResponse = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<message:GenericData xmlns:message="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message" xmlns:generic="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic">
    <message:DataSet>
        <generic:Obs>
            <generic:ObsDimension value="2024-06-14"/>
            <generic:ObsValue value="0.85"/>
        </generic:Obs>
    </message:DataSet>
</message:GenericData>
XML;
});

test('constructs valid SDMX URL for currency pair and date', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($this->validEcbResponse, 200),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));

    Http::assertSent(function ($request): bool {
        $url = $request->url();

        return str_contains($url, 'data-api.ecb.europa.eu')
            && str_contains($url, 'EXR')
            && str_contains($url, 'USD');
    });
});

test('parses ECB SDMX response correctly', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($this->validEcbResponse, 200),
    ]);

    $result = $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['rate', 'date'])
        ->and($result['rate'])->toBeFloat()
        ->and($result['date'])->toBeString();
});

test('handles HTTP 404 for missing date', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response('Not Found', 404),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-15'));
})->throws(FxRateException::class, 'No rate available');

test('handles HTTP 500 server error', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response('Server Error', 500),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ECB API error');

test('handles network timeout', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => fn () => throw new ConnectionException('Timeout'),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class);

test('handles malformed XML response', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response('not valid xml', 200),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class);

test('handles response with no observations', function (): void {
    $noObservations = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<message:GenericData xmlns:message="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message" xmlns:generic="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic">
    <message:DataSet>
    </message:DataSet>
</message:GenericData>
XML;

    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($noObservations, 200),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'No rate found');

test('handles response with missing rate value', function (): void {
    $missingValue = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<message:GenericData xmlns:message="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/message" xmlns:generic="http://www.sdmx.org/resources/sdmxml/schemas/v2_1/data/generic">
    <message:DataSet>
        <generic:Obs>
            <generic:ObsDimension value="2024-06-14"/>
            <generic:ObsValue />
        </generic:Obs>
    </message:DataSet>
</message:GenericData>
XML;

    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($missingValue, 200),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'No rate value');

test('handles unsupported currency pair', function (): void {
    $this->ecbApiService->getRate('XXX', 'YYY', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'Unsupported currency');

test('handles request exceptions', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => function (): void {
            $response = new Response(
                new GuzzleHttp\Psr7\Response(500)
            );

            throw new RequestException($response);
        },
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'ECB API error');

test('retries on temporary failure', function (): void {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount < 3) {
            return Http::response('Server Error', 500);
        }

        return Http::response($this->validEcbResponse, 200);
    });

    $result = $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));

    expect($callCount)->toBe(3)
        ->and($result)->toBeArray();
});

test('caches response for same request', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response($this->validEcbResponse, 200),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));

    Http::assertSentCount(1);
});

test('respects ECB API rate limits', function (): void {
    Http::fake([
        'data-api.ecb.europa.eu/*' => Http::response('Too Many Requests', 429),
    ]);

    $this->ecbApiService->getRate('USD', 'EUR', Date::parse('2024-06-14'));
})->throws(FxRateException::class, 'rate limit');
