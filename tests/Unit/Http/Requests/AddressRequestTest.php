<?php

declare(strict_types=1);
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Support\Facades\Validator;

// --- 4.1 StoreAddressRequest validates country with Enum rule ---

test('StoreAddressRequest validates country with Enum rule', function (): void {
    $request = new StoreAddressRequest();
    $rules = $request->rules();

    $validator = Validator::make(
        ['street' => 'x', 'city' => 'x', 'state' => 'x', 'postal_code' => 'x', 'country' => 'INVALID_CODE'],
        $rules
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('country'))->toBeTrue();
});

// --- 4.2 UpdateAddressRequest validates country with Enum rule ---

test('UpdateAddressRequest validates country with Enum rule', function (): void {
    $request = new UpdateAddressRequest();
    $rules = $request->rules();

    $validator = Validator::make(
        ['street' => 'x', 'city' => 'x', 'state' => 'x', 'postal_code' => 'x', 'country' => 'INVALID_CODE'],
        $rules
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('country'))->toBeTrue();
});
