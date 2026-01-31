<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $address = $this->route('address');
        if ($address) {
            return auth()->user()->id === $address->user_id;
        }

        // For Livewire usage, authorization will be handled separately
        return true;
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
    public function validated(): array
    {
        return parent::validated();
    }
}
