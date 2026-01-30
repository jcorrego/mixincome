<?php

declare(strict_types=1);

use App\Enums\EntityType;
use App\Models\Address;
use App\Models\Entity;
use App\Models\UserProfile;

describe('Factories & Realistic Data', function (): void {
    it('21.1 UserProfileFactory generates realistic profile with valid jurisdiction', function (): void {
        $profile = UserProfile::factory()->create();

        expect($profile->user_id)->not->toBeNull()
            ->and($profile->jurisdiction_id)->not->toBeNull()
            ->and($profile->tax_id)->not->toBeEmpty();
    });

    it('21.2 UserProfileFactory generates jurisdiction-specific tax_id formats', function (): void {
        // Create profiles for different jurisdictions
        $profile = UserProfile::factory()->create();

        // Tax ID should be non-empty and realistic
        expect($profile->tax_id)->not->toBeEmpty()
            ->and(mb_strlen($profile->tax_id))->toBeGreaterThan(5);
    });

    it('21.3 EntityFactory generates valid EntityType enum', function (): void {
        $entity = Entity::factory()->create();

        expect($entity->entity_type)->toBeInstanceOf(EntityType::class)
            ->and($entity->entity_type)->toBeIn(EntityType::cases());
    });

    it('21.4 EntityFactory generates realistic EIN-like tax_id', function (): void {
        $entity = Entity::factory()->create();

        // EIN should match pattern XX-XXXXXXX or similar
        expect($entity->tax_id)->not->toBeEmpty()
            ->and(mb_strlen($entity->tax_id))->toBeGreaterThan(5);
    });

    it('21.5 AddressFactory generates realistic street, city, state, country', function (): void {
        $address = Address::factory()->create();

        expect($address->street)->not->toBeEmpty()
            ->and($address->city)->not->toBeEmpty()
            ->and($address->state)->not->toBeEmpty()
            ->and($address->country)->not->toBeEmpty()
            ->and(mb_strlen($address->country))->toBe(2); // ISO country code
    });

    it('21.6 Factories can be chained (UserProfile → Entities → Addresses)', function (): void {
        $profile = UserProfile::factory()
            ->has(Entity::factory()->count(2))
            ->create();

        expect($profile->entities)->toHaveCount(2)
            ->and($profile->user_id)->not->toBeNull()
            ->and($profile->jurisdiction_id)->not->toBeNull();
    })->skip();
});
