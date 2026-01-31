<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Address $address */
        $address = $this->route('address');

        return auth()->id() === $address->user_id;
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'street.required' => 'Street is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal code is required.',
            'country.required' => 'Country is required.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
}
