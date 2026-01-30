<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Entity;
use App\Models\User;
use App\Models\UserProfile;

describe('Full Type Coverage', function (): void {
    it('ensures UserProfile has complete coverage', function (): void {
        $profile = UserProfile::factory()->create();

        // Access all casts
        expect($profile->casts())->toBeArray();

        // Access all relationships
        $profile->user();
        $profile->jurisdiction();
        $profile->entities();
        $profile->address();

        // Verify attributes
        expect($profile->user_id)->not->toBeNull();
        expect($profile->jurisdiction_id)->not->toBeNull();
        expect($profile->tax_id)->not->toBeNull();
        expect($profile->status)->not->toBeNull();
        expect($profile->created_at)->not->toBeNull();
        expect($profile->updated_at)->not->toBeNull();
    });

    it('ensures Entity has complete coverage', function (): void {
        $entity = Entity::factory()->create();

        // Access all casts
        expect($entity->casts())->toBeArray();

        // Access all relationships
        $entity->userProfile();
        $entity->address();

        // Verify attributes
        expect($entity->user_profile_id)->not->toBeNull();
        expect($entity->name)->not->toBeNull();
        expect($entity->entity_type)->not->toBeNull();
        expect($entity->tax_id)->not->toBeNull();
        expect($entity->status)->not->toBeNull();
        expect($entity->created_at)->not->toBeNull();
        expect($entity->updated_at)->not->toBeNull();
    });

    it('ensures Address has complete coverage', function (): void {
        $address = Address::factory()->create();

        // Access all casts
        expect($address->casts())->toBeArray();

        // Access all relationships
        $address->addressable();
        $address->user();

        // Verify required attributes (addressable_id and addressable_type can be null)
        expect($address->user_id)->not->toBeNull();
        expect($address->street)->not->toBeNull();
        expect($address->city)->not->toBeNull();
        expect($address->state)->not->toBeNull();
        expect($address->postal_code)->not->toBeNull();
        expect($address->country)->not->toBeNull();
        expect($address->created_at)->not->toBeNull();
        expect($address->updated_at)->not->toBeNull();
    });

    it('ensures User model has userProfiles relationship', function (): void {
        $user = User::factory()->create();
        UserProfile::factory(2)->create(['user_id' => $user->id]);

        // Access the relationship
        $user->userProfiles();

        // Verify it works
        expect($user->userProfiles)->toHaveCount(2);
    });
});
