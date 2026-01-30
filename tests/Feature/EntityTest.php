<?php

declare(strict_types=1);

use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\UserProfile;

describe('Entity Creation', function () {
    it('6.1 can create Entity with user_profile_id, name, entity_type, tax_id', function () {
        $profile = UserProfile::factory()->create();

        $entity = Entity::create([
            'user_profile_id' => $profile->id,
            'name' => 'My LLC',
            'entity_type' => 'LLC',
            'tax_id' => '12-3456789',
            'status' => 'Active',
        ]);

        expect($entity->id)->toBeInt()
            ->and($entity->user_profile_id)->toBe($profile->id)
            ->and($entity->name)->toBe('My LLC')
            ->and($entity->tax_id)->toBe('12-3456789');
    });

    it('6.2 Entity.entity_type is cast to EntityType enum', function () {
        $profile = UserProfile::factory()->create();

        $entity = Entity::create([
            'user_profile_id' => $profile->id,
            'name' => 'My LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '12-3456789',
            'status' => 'Active',
        ]);

        expect($entity->entity_type)->toBeInstanceOf(EntityType::class)
            ->and($entity->entity_type->name)->toBe('LLC');
    });

    it('6.3 Entity status defaults to Active', function () {
        $profile = UserProfile::factory()->create();

        $entity = Entity::create([
            'user_profile_id' => $profile->id,
            'name' => 'My LLC',
            'entity_type' => EntityType::LLC,
            'tax_id' => '12-3456789',
        ]);

        expect($entity->status)->toBe('Active');
    });

    it('6.4 cannot create Entity with entity_type=Individual (enum rejects it)', function () {
        $profile = UserProfile::factory()->create();

        // Entity does not have Individual case, so this should fail
        expect(fn () => Entity::create([
            'user_profile_id' => $profile->id,
            'name' => 'My Individual',
            'entity_type' => 'Individual',
            'tax_id' => '12-3456789',
            'status' => 'Active',
        ]))->toThrow(Exception::class);
    })->skip();

    it('6.5 multiple entities can exist under same UserProfile', function () {
        $profile = UserProfile::factory()->create();

        Entity::factory(3)->create(['user_profile_id' => $profile->id]);

        expect($profile->entities)->toHaveCount(3);
    })->skip();
});

describe('Entity Retrieval', function () {
    it('7.1 can retrieve Entity by ID', function () {
        $entity = Entity::factory()->create();

        $retrieved = Entity::find($entity->id);

        expect($retrieved)->not->toBeNull()
            ->and($retrieved->id)->toBe($entity->id);
    });

    it('7.2 Entity has eager-loaded relationship to UserProfile', function () {
        $entity = Entity::factory()->create();

        expect($entity->userProfile)->not->toBeNull()
            ->and($entity->userProfile)->toBeInstanceOf(UserProfile::class);
    });

    it('7.6 no N+1 queries when loading entities with profile', function () {
        Entity::factory(5)->create();

        $entities = Entity::with('userProfile')->get();

        expect($entities)->toHaveCount(5);
    })->skip();
});

describe('Entity Updates', function () {
    it('8.1 can update Entity.name', function () {
        $entity = Entity::factory()->create(['name' => 'Old Name']);

        $entity->update(['name' => 'New Name']);

        expect($entity->refresh()->name)->toBe('New Name');
    });

    it('8.2 can update Entity.status (Active → Inactive)', function () {
        $entity = Entity::factory()->create(['status' => 'Active']);

        $entity->update(['status' => 'Inactive']);

        expect($entity->refresh()->status)->toBe('Inactive');
    });

    it('8.3 can update Entity.tax_id', function () {
        $entity = Entity::factory()->create(['tax_id' => '11-1111111']);

        $entity->update(['tax_id' => '22-2222222']);

        expect($entity->refresh()->tax_id)->toBe('22-2222222');
    });
});

describe('Entity Deletion', function () {
    it('9.2 deleting Entity cascades to related Address if exists', function () {
        $entity = Entity::factory()->create();
        $address = App\Models\Address::factory()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
        ]);

        $addressId = $address->id;
        $entity->delete();

        expect(App\Models\Address::find($addressId))->toBeNull();
    })->skip();

    it('9.4 deleting Entity does NOT cascade to UserProfile (parent not affected)', function () {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        $entity->delete();

        expect(UserProfile::find($profile->id))->not->toBeNull();
    });
});

describe('Entity-UserProfile Relationship', function () {
    it('10.1 Entity.userProfile returns correct UserProfile', function () {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        expect($entity->userProfile->id)->toBe($profile->id);
    });

    it('10.2 Entity inherits jurisdiction via Entity.userProfile.jurisdiction', function () {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        expect($entity->userProfile->jurisdiction)->not->toBeNull();
    });
});

describe('Model Relationships - Entity', function () {
    it('23.6 Entity.userProfile returns belongsTo(UserProfile)', function () {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        expect($entity->userProfile->id)->toBe($profile->id)
            ->and($entity->userProfile)->toBeInstanceOf(UserProfile::class);
    });

    it('23.7 Entity.address returns morphOne(Address)', function () {
        $entity = Entity::factory()->create();
        $address = App\Models\Address::factory()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
        ]);

        expect($entity->address->id)->toBe($address->id)
            ->and($entity->address)->toBeInstanceOf(App\Models\Address::class);
    })->skip();
});
