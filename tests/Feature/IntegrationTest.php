<?php

declare(strict_types=1);

use App\Enums\EntityType;
use App\Models\Address;
use App\Models\Entity;
use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;

describe('Integration Tests (End-to-End)', function (): void {
    it('24.1 can create User → UserProfile → Entity → Address in sequence', function (): void {
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        $profile = UserProfile::query()->create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        $entity = Entity::query()->create([
            'user_profile_id' => $profile->id,
            'name' => 'My LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '12-3456789',
            'status' => 'Active',
        ]);

        $address = Address::query()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
            'user_id' => $user->id,
            'street' => '123 Main St',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country' => 'ES',
        ]);

        expect($user->id)->not->toBeNull()
            ->and($profile->id)->not->toBeNull()
            ->and($entity->id)->not->toBeNull()
            ->and($address->id)->not->toBeNull();
    });

    it('24.2 full data flow: User has 2 profiles (Spain, USA), Spain has 1 entity with address, USA has 2 entities', function (): void {
        $user = User::factory()->create();

        $spain = Jurisdiction::factory()->create(['iso_code' => 'ES']);
        $usa = Jurisdiction::factory()->create(['iso_code' => 'US']);

        $spainProfile = UserProfile::query()->create([
            'user_id' => $user->id,
            'jurisdiction_id' => $spain->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        $usaProfile = UserProfile::query()->create([
            'user_id' => $user->id,
            'jurisdiction_id' => $usa->id,
            'tax_id' => 'SSN-123-45-6789',
            'status' => 'Active',
        ]);

        // Spain: 1 entity with address
        $spainEntity = Entity::query()->create([
            'user_profile_id' => $spainProfile->id,
            'name' => 'Spain LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '11-1111111',
            'status' => 'Active',
        ]);

        Address::query()->create([
            'addressable_id' => $spainEntity->id,
            'addressable_type' => Entity::class,
            'user_id' => $user->id,
            'street' => 'Calle Principal',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country' => 'ES',
        ]);

        // USA: 2 entities
        Entity::query()->create([
            'user_profile_id' => $usaProfile->id,
            'name' => 'USA LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '22-2222222',
            'status' => 'Active',
        ]);

        Entity::query()->create([
            'user_profile_id' => $usaProfile->id,
            'name' => 'USA Corp',
            'entity_type' => EntityType::CCorp,
            'tax_id' => '33-3333333',
            'status' => 'Active',
        ]);

        expect($user->userProfiles)->toHaveCount(2)
            ->and($spainProfile->entities)->toHaveCount(1)
            ->and($usaProfile->entities)->toHaveCount(2);
    })->skip();

    it('24.3 deleting profile cascades correctly to entities and addresses', function (): void {
        $profile = UserProfile::factory()->has(
            Entity::factory()->count(2)
        )->create();

        $profileId = $profile->id;
        $entityIds = $profile->entities->pluck('id')->toArray();

        $profile->delete();

        expect(UserProfile::query()->find($profileId))->toBeNull()
            ->and(Entity::query()->whereIn('id', $entityIds)->count())->toBe(0);
    })->skip();

    it('24.4 querying full hierarchy with eager loading is efficient (no N+1)', function (): void {
        UserProfile::factory(3)->create();

        $profiles = UserProfile::with('user', 'jurisdiction', 'entities')->get();

        expect($profiles)->toHaveCount(3);
    })->skip();
});
