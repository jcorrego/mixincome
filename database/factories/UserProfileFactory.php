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
        return [
            'user_id' => User::factory(),
            'jurisdiction_id' => Jurisdiction::factory(),
            'tax_id' => $this->faker->numerify('##########'),
            'status' => 'Active',
        ];
    }

    /**
     * Spanish profile with NIF tax ID
     */
    public function spain(): static
    {
        return $this->state([
            'jurisdiction_id' => Jurisdiction::factory()->spain(),
            'tax_id' => 'NIF'.$this->faker->numerify('#########'),
        ]);
    }

    /**
     * US profile with EIN-style tax ID
     */
    public function usa(): static
    {
        return $this->state([
            'jurisdiction_id' => Jurisdiction::factory()->usa(),
            'tax_id' => $this->faker->bothify('??-#######'),
        ]);
    }

    /**
     * Colombian profile with RUT-style tax ID
     */
    public function colombia(): static
    {
        return $this->state([
            'jurisdiction_id' => Jurisdiction::factory()->colombia(),
            'tax_id' => $this->faker->numerify('##########-#'),
        ]);
    }
}
