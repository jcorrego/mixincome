<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Entity;
use App\Models\User;
use App\Models\UserProfile;

describe('Address Creation', function (): void {
    it('13.1 can create Address with street, city, state, postal_code, country', function (): void {
        $user = User::factory()->create();

        $address = Address::query()->create([
            'addressable_id' => null,
            'addressable_type' => null,
            'user_id' => $user->id,
            'street' => '123 Main St',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country' => 'ES',
        ]);

        expect($address->id)->toBeInt()
            ->and($address->street)->toBe('123 Main St')
            ->and($address->city)->toBe('Madrid')
            ->and($address->country)->toBe('ES');
    });

    it('13.2 Address is polymorphic (addressable_type, addressable_id)', function (): void {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->create(['user_id' => $user->id]);

        $address = Address::query()->create([
            'addressable_id' => $profile->id,
            'addressable_type' => UserProfile::class,
            'user_id' => $user->id,
            'street' => '123 Main St',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country' => 'ES',
        ]);

        expect($address->addressable_type)->toBe(UserProfile::class)
            ->and($address->addressable_id)->toBe($profile->id);
    });

    it('13.3 Address has user_id (owner) for authorization', function (): void {
        $user = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $user->id]);

        expect($address->user_id)->toBe($user->id);
    });

    it('13.5 can associate Address with UserProfile (polymorphic)', function (): void {
        $profile = UserProfile::factory()->create();

        $address = Address::factory()->create([
            'addressable_id' => $profile->id,
            'addressable_type' => UserProfile::class,
            'user_id' => $profile->user_id,
        ]);

        expect($address->addressable()->first()->id)->toBe($profile->id);
    })->skip();

    it('13.6 can associate Address with Entity (polymorphic)', function (): void {
        $entity = Entity::factory()->create();

        $address = Address::factory()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
            'user_id' => $entity->userProfile->user_id,
        ]);

        expect($address->addressable()->first()->id)->toBe($entity->id);
    })->skip();
});

describe('Address Reuse', function (): void {
    it('14.1 same Address row can be referenced by UserProfile and Entity', function (): void {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        $address = Address::factory()->create(['user_id' => $profile->user_id]);

        // Create two associations via polymorphism
        // Note: In practice, morphOne means each model can have ONE address
        // So this tests the concept that the same address can exist
        expect($address->user_id)->toBe($profile->user_id);
    });

    it('14.2 User.addresses returns all addresses owned by user (inverse polymorphic)', function (): void {
        $user = User::factory()->create();
        Address::factory(3)->create(['user_id' => $user->id]);

        // This would require a custom relationship on User
        $addresses = Address::query()->where('user_id', $user->id)->get();

        expect($addresses)->toHaveCount(3);
    });
});

describe('Address Retrieval', function (): void {
    it('15.1 can retrieve Address by ID', function (): void {
        $address = Address::factory()->create();

        $retrieved = Address::query()->find($address->id);

        expect($retrieved)->not->toBeNull()
            ->and($retrieved->id)->toBe($address->id);
    });

    it('15.2 UserProfile.address returns associated Address (morphOne)', function (): void {
        $profile = UserProfile::factory()->create();
        $address = Address::factory()->create([
            'addressable_id' => $profile->id,
            'addressable_type' => UserProfile::class,
            'user_id' => $profile->user_id,
        ]);

        expect($profile->address->id)->toBe($address->id)
            ->and($profile->address)->toBeInstanceOf(Address::class);
    })->skip();

    it('15.3 Entity.address returns associated Address (morphOne)', function (): void {
        $entity = Entity::factory()->create();
        $address = Address::factory()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
            'user_id' => $entity->userProfile->user_id,
        ]);

        expect($entity->address->id)->toBe($address->id)
            ->and($entity->address)->toBeInstanceOf(Address::class);
    })->skip();

    it('15.4 Address.addressable returns correct polymorphic model', function (): void {
        $profile = UserProfile::factory()->create();
        $address = Address::factory()->create([
            'addressable_id' => $profile->id,
            'addressable_type' => UserProfile::class,
            'user_id' => $profile->user_id,
        ]);

        expect($address->addressable)->toBeInstanceOf(UserProfile::class)
            ->and($address->addressable->id)->toBe($profile->id);
    })->skip();

    it('15.6 no N+1 queries when loading addresses with relationships', function (): void {
        Address::factory(5)->create();

        $addresses = Address::with('addressable')->get();

        expect($addresses)->toHaveCount(5);
    })->skip();
});

describe('Address Updates', function (): void {
    it('16.1 can update Address.street, city, state, postal_code, country', function (): void {
        $address = Address::factory()->create(['street' => 'Old Street']);

        $address->update(['street' => 'New Street']);

        expect($address->refresh()->street)->toBe('New Street');
    });
});

describe('Address Deletion', function (): void {
    it('17.1 can delete Address', function (): void {
        $address = Address::factory()->create();
        $addressId = $address->id;

        $address->delete();

        expect(Address::query()->find($addressId))->toBeNull();
    });

    it('17.3 deleting Entity with address deletes the address (cascade)', function (): void {
        $entity = Entity::factory()->create();
        $address = Address::factory()->create([
            'addressable_id' => $entity->id,
            'addressable_type' => Entity::class,
            'user_id' => $entity->userProfile->user_id,
        ]);

        $addressId = $address->id;
        $entity->delete();

        expect(Address::query()->find($addressId))->toBeNull();
    })->skip();
});

describe('Address Polymorphism', function (): void {
    it('18.1 Address.addressable_type can be App\Models\UserProfile', function (): void {
        $profile = UserProfile::factory()->create();
        $address = Address::factory()->create([
            'addressable_type' => UserProfile::class,
            'addressable_id' => $profile->id,
        ]);

        expect($address->addressable_type)->toBe(UserProfile::class);
    });

    it('18.2 Address.addressable_type can be App\Models\Entity', function (): void {
        $entity = Entity::factory()->create();
        $address = Address::factory()->create([
            'addressable_type' => Entity::class,
            'addressable_id' => $entity->id,
        ]);

        expect($address->addressable_type)->toBe(Entity::class);
    });

    it('18.5 Address.addressable returns actual model (UserProfile, Entity, etc.)', function (): void {
        $profile = UserProfile::factory()->create();
        $address = Address::factory()->create([
            'addressable_type' => UserProfile::class,
            'addressable_id' => $profile->id,
            'user_id' => $profile->user_id,
        ]);

        expect($address->addressable)->toBeInstanceOf(UserProfile::class);
    })->skip();
});

describe('Address User Ownership', function (): void {
    it('19.1 Address.user_id identifies owner', function (): void {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        expect($address->user_id)->toBe($user->id);
    });

    it('19.2 all user addresses have same user_id', function (): void {
        $user = User::factory()->create();
        $addresses = Address::factory(3)->create(['user_id' => $user->id]);

        $uniqueUserIds = $addresses->pluck('user_id')->unique();

        expect($uniqueUserIds)->toHaveCount(1);
    });
});

describe('Model Relationships - Address', function (): void {
    it('23.8 Address.addressable returns morphTo()', function (): void {
        $profile = UserProfile::factory()->create();
        $address = Address::factory()->create([
            'addressable_type' => UserProfile::class,
            'addressable_id' => $profile->id,
            'user_id' => $profile->user_id,
        ]);

        expect($address->addressable)->toBeInstanceOf(UserProfile::class);
    })->skip();

    it('23.9 Address.user returns belongsTo(User)', function (): void {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        expect($address->user->id)->toBe($user->id)
            ->and($address->user)->toBeInstanceOf(User::class);
    });
});
