<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class FetchFxRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_currency_id' => ['required', 'exists:currencies,id'],
            'to_currency_id' => [
                'required',
                'exists:currencies,id',
                'different:from_currency_id',
            ],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_currency_id.required' => 'From currency is required.',
            'from_currency_id.exists' => 'Invalid from currency selected.',
            'to_currency_id.required' => 'To currency is required.',
            'to_currency_id.exists' => 'Invalid to currency selected.',
            'to_currency_id.different' => 'From and to currencies must be different.',
            'date.required' => 'Date is required.',
            'date.date' => 'Invalid date format.',
            'date.before_or_equal' => 'Date cannot be in the future.',
        ];
    }
}
