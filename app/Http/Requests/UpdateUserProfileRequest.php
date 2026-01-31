<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $profile = $this->route('userProfile');
        if ($profile) {
            return auth()->user()->id === $profile->user_id;
        }

        // For Livewire usage, authorization will be handled separately
        return true;
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function rules(): array
    {
        $profile = $this->route('userProfile');
        $profileId = $profile?->id ?? $this->input('user_profile_id');

        return [
            'jurisdiction_id' => ['required', 'integer', 'exists:jurisdictions,id'],
            'tax_id' => [
                'required',
                'string',
                Rule::unique('user_profiles')
                    ->ignore($profileId)
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
            'tax_id.required' => 'Tax ID is required.',
            'tax_id.unique' => 'A tax profile for this jurisdiction already exists with this tax ID.',
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
