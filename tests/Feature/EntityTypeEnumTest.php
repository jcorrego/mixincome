<?php

declare(strict_types=1);

use App\Enums\EntityType;

describe('Enum Tests', function () {
    it('25.1 EntityType enum can be instantiated with LLC', function () {
        $entityType = EntityType::LLC;

        expect($entityType)->toBeInstanceOf(EntityType::class)
            ->and($entityType->name)->toBe('LLC');
    });

    it('25.2 EntityType enum can be instantiated with SCorp', function () {
        $entityType = EntityType::SCorp;

        expect($entityType)->toBeInstanceOf(EntityType::class)
            ->and($entityType->name)->toBe('SCorp');
    });

    it('25.3 EntityType cannot be instantiated with Individual (throws error or validation fails)', function () {
        // Individual is not a case in EntityType, so trying to use it should fail
        expect(fn () => EntityType::from('Individual'))->toThrow(Exception::class);
    })->skip();

    it('25.4 EntityType.cases() returns all valid types', function () {
        $cases = EntityType::cases();

        expect($cases)->toHaveCount(6) // LLC, SCorp, CCorp, Partnership, Trust, Other
            ->and($cases[0]->name)->toBeIn(['LLC', 'SCorp', 'CCorp', 'Partnership', 'Trust', 'Other']);
    });

    it('11.1 EntityType enum contains: LLC, SCorp, CCorp, Partnership, Trust, Other', function () {
        $cases = EntityType::cases();
        $names = array_map(fn ($case) => $case->name, $cases);

        expect($names)->toContain('LLC')
            ->toContain('SCorp')
            ->toContain('CCorp')
            ->toContain('Partnership')
            ->toContain('Trust')
            ->toContain('Other');
    });

    it('11.2 EntityType enum does NOT contain Individual', function () {
        $cases = EntityType::cases();
        $names = array_map(fn ($case) => $case->name, $cases);

        expect($names)->not->toContain('Individual');
    });

    it('11.3 Entity.entity_type is type-safe (enum, not string)', function () {
        $profile = App\Models\UserProfile::factory()->create();
        $entity = App\Models\Entity::create([
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
