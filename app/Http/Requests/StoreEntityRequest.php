<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EntityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreEntityRequest extends FormRequest
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
            'user_profile_id' => ['required', 'integer', 'exists:user_profiles,id'],
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['required', new Enum(EntityType::class)],
            'tax_id' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_profile_id.required' => 'User profile is required.',
            'user_profile_id.exists' => 'The selected user profile does not exist.',
            'name.required' => 'Entity name is required.',
            'entity_type.required' => 'Entity type is required.',
            'entity_type.enum' => 'Entity type must be one of: '.implode(', ', array_map(fn (EntityType $type) => $type->value, EntityType::cases())).'.',
            'tax_id.required' => 'Tax ID is required.',
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
