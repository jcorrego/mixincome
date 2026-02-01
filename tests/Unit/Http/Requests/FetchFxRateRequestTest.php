<?php

declare(strict_types=1);

use App\Http\Requests\FetchFxRateRequest;
use App\Models\Currency;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
    $this->usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $this->eur = Currency::query()->where('code', 'EUR')->firstOrFail();
});

test('validates required fields', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('from_currency_id'))->toBeTrue()
        ->and($validator->errors()->has('to_currency_id'))->toBeTrue()
        ->and($validator->errors()->has('date'))->toBeTrue();
});

test('validates from_currency_id exists', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => 9999,
        'to_currency_id' => $this->eur->id,
        'date' => '2024-06-14',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('from_currency_id'))->toBeTrue();
});

test('validates to_currency_id exists', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => 9999,
        'date' => '2024-06-14',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('to_currency_id'))->toBeTrue();
});

test('validates currencies must be different', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->usd->id,
        'date' => '2024-06-14',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('to_currency_id'))->toBeTrue();
});

test('validates date must be valid', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'date' => 'invalid-date',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('date'))->toBeTrue();
});

test('validates date must not be in future', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'date' => Date::tomorrow()->toDateString(),
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('date'))->toBeTrue();
});

test('validates date can be today', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'date' => Date::today()->toDateString(),
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('validates date can be in past', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'date' => '2024-06-14',
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('passes validation with valid data', function (): void {
    $request = new FetchFxRateRequest();
    $validator = Validator::make([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'date' => '2024-06-14',
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('has custom error messages', function (): void {
    $request = new FetchFxRateRequest();
    $messages = $request->messages();

    expect($messages)->toHaveKey('from_currency_id.required')
        ->and($messages)->toHaveKey('to_currency_id.different')
        ->and($messages)->toHaveKey('date.required');
});

test('authorize returns true', function (): void {
    $request = new FetchFxRateRequest();

    expect($request->authorize())->toBeTrue();
});
