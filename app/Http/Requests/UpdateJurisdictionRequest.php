<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Jurisdiction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateJurisdictionRequest extends FormRequest
{
    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function rules(): array
    {
        /** @var Jurisdiction|null $jurisdiction */
        $jurisdiction = $this->route('jurisdiction');

        $jurisdictionId = $this->input('jurisdiction_id') ?? $jurisdiction?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'iso_code' => ['required', 'string', 'min:2', 'max:3', Rule::unique('jurisdictions', 'iso_code')->ignore($jurisdictionId)],
            'timezone' => ['required', 'string', 'timezone'],
            'default_currency' => ['required', 'string', 'size:3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Jurisdiction name is required.',
            'iso_code.required' => 'ISO code is required.',
            'iso_code.unique' => 'This ISO code already exists.',
            'timezone.required' => 'Timezone is required.',
            'timezone.timezone' => 'The timezone must be a valid timezone.',
            'default_currency.required' => 'Default currency is required.',
            'default_currency.size' => 'Default currency must be 3 characters.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
}
