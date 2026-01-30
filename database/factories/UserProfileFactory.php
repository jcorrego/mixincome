<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jurisdiction = Jurisdiction::factory()->create();

        // Generate tax ID based on jurisdiction
        $taxId = match ($jurisdiction->code) {
            'ES' => 'NIF'.$this->faker->numerify('#########'),
            'US' => $this->faker->bothify('??-#######'),
            'CO' => $this->faker->numerify('##########-#'),
            default => $this->faker->numerify('##########'),
        };

        return [
            'user_id' => User::factory(),
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => $taxId,
            'status' => 'Active',
        ];
    }
}
