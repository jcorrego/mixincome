<?php

declare(strict_types=1);

use App\Models\Entity;
use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Schema;

describe('Database Constraints & Indexes', function (): void {
    it('20.1 migration creates user_profiles table with correct columns', function (): void {
        expect(Schema::hasTable('user_profiles'))->toBeTrue();
        expect(Schema::hasColumns('user_profiles', [
            'id',
            'user_id',
            'jurisdiction_id',
            'address_id',
            'tax_id',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
    });

    it('20.2 migration creates unique index on (user_id, jurisdiction_id)', function (): void {
        // This would be tested by attempting duplicate insertion
        $user = User::factory()->create();
        $jurisdiction = Jurisdiction::factory()->create();

        UserProfile::query()->create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF123456789',
            'status' => 'Active',
        ]);

        // Try to create duplicate
        expect(fn () => UserProfile::query()->create([
            'user_id' => $user->id,
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'NIF999999999',
            'status' => 'Active',
        ]))->toThrow(Exception::class);
    });

    it('20.3 migration creates entities table with correct columns', function (): void {
        expect(Schema::hasTable('entities'))->toBeTrue();
        expect(Schema::hasColumns('entities', [
            'id',
            'user_profile_id',
            'address_id',
            'name',
            'entity_type',
            'tax_id',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
    });

    it('20.4 migration creates addresses table with user_id foreign key', function (): void {
        expect(Schema::hasTable('addresses'))->toBeTrue();
        expect(Schema::hasColumns('addresses', [
            'id',
            'user_id',
            'street',
            'city',
            'state',
            'postal_code',
            'country',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
    });

    it('20.5 foreign keys are correctly configured (cascade deletes)', function (): void {
        $profile = UserProfile::factory()->create();
        $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

        $profile->delete();

        expect(Entity::query()->find($entity->id))->toBeNull();
    });
});
