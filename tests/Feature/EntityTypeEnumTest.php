<?php

declare(strict_types=1);

use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\UserProfile;

describe('Enum Tests', function (): void {
    it('25.1 EntityType enum can be instantiated with LLC', function (): void {
        $entityType = EntityType::LLC;

        expect($entityType)->toBeInstanceOf(EntityType::class)
            ->and($entityType->name)->toBe('LLC');
    });

    it('25.2 EntityType enum can be instantiated with SCorp', function (): void {
        $entityType = EntityType::SCorp;

        expect($entityType)->toBeInstanceOf(EntityType::class)
            ->and($entityType->name)->toBe('SCorp');
    });

    it('25.3 EntityType cannot be instantiated with Individual (throws error or validation fails)', function (): void {
        // Individual is not a case in EntityType, so trying to use it should fail
        expect(fn () => EntityType::from('Individual'))->toThrow(ValueError::class);
    });

    it('25.4 EntityType.cases() returns all valid types', function (): void {
        $cases = EntityType::cases();

        expect($cases)->toHaveCount(6) // LLC, SCorp, CCorp, Partnership, Trust, Other
            ->and($cases[0]->name)->toBeIn(['LLC', 'SCorp', 'CCorp', 'Partnership', 'Trust', 'Other']);
    });

    it('11.1 EntityType enum contains: LLC, SCorp, CCorp, Partnership, Trust, Other', function (): void {
        $cases = EntityType::cases();
        $names = array_map(fn (EntityType $case) => $case->name, $cases);

        expect($names)->toContain('LLC')
            ->toContain('SCorp')
            ->toContain('CCorp')
            ->toContain('Partnership')
            ->toContain('Trust')
            ->toContain('Other');
    });

    it('11.2 EntityType enum does NOT contain Individual', function (): void {
        $cases = EntityType::cases();
        $names = array_map(fn (EntityType $case) => $case->name, $cases);

        expect($names)->not->toContain('Individual');
    });

    it('11.3 Entity.entity_type is type-safe (enum, not string)', function (): void {
        $profile = UserProfile::factory()->create();
        $entity = Entity::query()->create([
            'user_profile_id' => $profile->id,
            'name' => 'My LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '12-3456789',
            'status' => 'Active',
        ]);

        expect($entity->entity_type)->toBeInstanceOf(EntityType::class)
            ->and($entity->entity_type)->toBe(EntityType::LLC);
    });
});
