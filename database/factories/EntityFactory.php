<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
final class EntityFactory extends Factory
{
    protected $model = Entity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(),
            'name' => $this->faker->company(),
            'entity_type' => $this->faker->randomElement(EntityType::cases()),
            'tax_id' => $this->faker->bothify('##-#######'),
            'status' => 'Active',
        ];
    }
}
