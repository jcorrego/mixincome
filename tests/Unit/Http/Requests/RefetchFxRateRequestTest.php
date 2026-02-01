<?php

declare(strict_types=1);

use App\Http\Requests\RefetchFxRateRequest;
use App\Models\FxRate;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
});

test('validates required fields', function (): void {
    $request = new RefetchFxRateRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('rate_id'))->toBeTrue();
});

test('validates rate_id must be integer', function (): void {
    $request = new RefetchFxRateRequest();
    $validator = Validator::make([
        'rate_id' => 'not-an-integer',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('rate_id'))->toBeTrue();
});

test('validates rate_id must exist in database', function (): void {
    $request = new RefetchFxRateRequest();
    $validator = Validator::make([
        'rate_id' => 9999,
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('rate_id'))->toBeTrue();
});

test('passes validation with valid rate_id', function (): void {
    $fxRate = FxRate::factory()->create([
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    $request = new RefetchFxRateRequest();
    $validator = Validator::make([
        'rate_id' => $fxRate->id,
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('has custom error messages', function (): void {
    $request = new RefetchFxRateRequest();
    $messages = $request->messages();

    expect($messages)->toHaveKey('rate_id.required')
        ->and($messages)->toHaveKey('rate_id.exists');
});

test('authorize returns true', function (): void {
    $request = new RefetchFxRateRequest();

    expect($request->authorize())->toBeTrue();
});
