<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            'jurisdiction_id' => [
                'required',
                'integer',
                'exists:jurisdictions,id',
                Rule::unique('user_profiles')
                    ->where('user_id', auth()->id()),
            ],
            'tax_id' => [
                'required',
                'string',
                Rule::unique('user_profiles')
                    ->where('user_id', auth()->id())
                    ->where('jurisdiction_id', $this->input('jurisdiction_id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jurisdiction_id.required' => 'Jurisdiction is required.',
            'jurisdiction_id.exists' => 'The selected jurisdiction does not exist.',
            'jurisdiction_id.unique' => 'You already have a profile for this jurisdiction.',
            'tax_id.required' => 'Tax ID is required.',
            'tax_id.unique' => 'A tax profile for this jurisdiction already exists with this tax ID.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
}
