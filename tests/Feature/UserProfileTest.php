<?php

declare(strict_types=1);

use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;

describe('UserProfile Creation', function () {
    it('1.1 can create UserProfile with user_id, jurisdiction_id, and tax_id', function () {
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        expect($profile->id)->toBeInt()
            ->and($profile->user_id)->toBe($user->id)
            ->and($profile->jurisdiction_id)->toBe($jurisdiction->id)
            ->and($profile->tax_id)->toBe('NIF123456789');
    });

    it('1.2 UserProfile status defaults to Active', function () {
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
        ]);

        expect($profile->status)->toBe('Active');
    });

    it('1.3 timestamps (created_at, updated_at) are auto-generated', function () {
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        expect($profile->created_at)->not->toBeNull()
            ->and($profile->updated_at)->not->toBeNull();
    });

    it('1.4 unique constraint (user_id, jurisdiction_id) prevents duplicates', function () {
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        UserProfile::create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        // This should fail due to unique constraint
        expect(fn () => UserProfile::create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF999999999',
            'status' => 'Active',
        ]))->toThrow(Exception::class);
    })->skip();
});

describe('UserProfile Retrieval', function () {
    it('2.1 can retrieve UserProfile by ID', function () {
        $profile = UserProfile::factory()->create();

        $retrieved = UserProfile::find($profile->id);

        expect($retrieved)->not->toBeNull()
            ->and($retrieved->id)->toBe($profile->id);
    });

    it('2.2 UserProfile has eager-loaded relationship to Jurisdiction', function () {
        $profile = UserProfile::factory()->create();

        expect($profile->jurisdiction)->not->toBeNull()
            ->and($profile->jurisdiction)->toBeInstanceOf(Jurisdiction::class);
    });

    it('2.5 no N+1 queries when loading profiles with jurisdiction', function () {
        UserProfile::factory(5)->create();

        $profiles = UserProfile::with('jurisdiction')->get();

        expect($profiles)->toHaveCount(5);
    })->skip();
});

describe('UserProfile Updates', function () {
    it('3.1 can update UserProfile.tax_id without creating duplicate profile', function () {
        $profile = UserProfile::factory()->create([
            'tax_id' => 'NIF123456789',
        ]);

        $profile->update(['tax_id' => 'NIF987654321']);

        expect($profile->refresh()->tax_id)->toBe('NIF987654321');
    });

    it('3.2 can update UserProfile.status (Active → Inactive)', function () {
        $profile = UserProfile::factory()->create(['status' => 'Active']);

        $profile->update(['status' => 'Inactive']);

        expect($profile->refresh()->status)->toBe('Inactive');
    });
});

describe('UserProfile Deletion', function () {
    it('4.1 deleting UserProfile cascades to related Entities', function () {
        $profile = UserProfile::factory()->has(
            App\Models\Entity::factory()->count(3)
        )->create();

        $profileId = $profile->id;
        $entityIds = $profile->entities->pluck('id')->toArray();

        $profile->delete();

        expect(App\Models\Entity::whereIn('id', $entityIds)->count())->toBe(0);
    })->skip();

    it('4.2 deleting UserProfile without entities works cleanly', function () {
        $profile = UserProfile::factory()->create();
        $profileId = $profile->id;

        $profile->delete();

        expect(UserProfile::find($profileId))->toBeNull();
    });
});

describe('UserProfile-Jurisdiction Relationship', function () {
    it('5.1 UserProfile.jurisdiction returns correct Jurisdiction model', function () {
        $jurisdiction = Jurisdiction::factory()->create();
        $profile = UserProfile::factory()->create(['jurisdiction_id' => $jurisdiction->id]);

        expect($profile->jurisdiction->id)->toBe($jurisdiction->id);
    });

    it('5.2 Jurisdiction.default_currency is accessible via profile', function () {
        $jurisdiction = Jurisdiction::factory()->create(['default_currency' => 'EUR']);
        $profile = UserProfile::factory()->create(['jurisdiction_id' => $jurisdiction->id]);

        expect($profile->jurisdiction->default_currency)->toBe('EUR');
    });
});

describe('Model Relationships - UserProfile', function () {
    it('23.1 User.userProfiles returns hasMany(UserProfile)', function () {
        $user = User::factory()->create();
        UserProfile::factory(3)->create(['user_id' => $user->id]);

        expect($user->userProfiles)->toHaveCount(3)
            ->and($user->userProfiles[0])->toBeInstanceOf(UserProfile::class);
    })->skip();

    it('23.2 UserProfile.user returns belongsTo(User)', function () {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->create(['user_id' => $user->id]);

        expect($profile->user->id)->toBe($user->id)
            ->and($profile->user)->toBeInstanceOf(User::class);
    });

    it('23.3 UserProfile.jurisdiction returns belongsTo(Jurisdiction)', function () {
        $jurisdiction = Jurisdiction::factory()->create();
        $profile = UserProfile::factory()->create(['jurisdiction_id' => $jurisdiction->id]);

        expect($profile->jurisdiction->id)->toBe($jurisdiction->id)
            ->and($profile->jurisdiction)->toBeInstanceOf(Jurisdiction::class);
    });

    it('23.4 UserProfile.entities returns hasMany(Entity)', function () {
        $profile = UserProfile::factory()->has(
            App\Models\Entity::factory()->count(2)
        )->create();

        expect($profile->entities)->toHaveCount(2)
            ->and($profile->entities[0])->toBeInstanceOf(App\Models\Entity::class);
    })->skip();

    it('23.5 UserProfile.address returns morphOne(Address)', function () {
        $profile = UserProfile::factory()->create();
        $address = App\Models\Address::factory()->create([
            'addressable_id' => $profile->id,
            'addressable_type' => UserProfile::class,
        ]);

        expect($profile->address->id)->toBe($address->id)
            ->and($profile->address)->toBeInstanceOf(App\Models\Address::class);
    })->skip();
});
