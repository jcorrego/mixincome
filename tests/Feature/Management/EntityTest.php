<?php

declare(strict_types=1);

use App\Enums\EntityType;
use App\Livewire\Management\Entities;
use App\Models\Address;
use App\Models\Entity;
use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;
use Livewire\Livewire;

// --- View Entities ---

test('authenticated user can view entities page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/management/entities')
        ->assertOk()
        ->assertSeeLivewire(Entities::class);
});

test('unauthenticated user is redirected to login from entities', function (): void {
    $this->get('/management/entities')
        ->assertRedirect('/login');
});

// --- Create Entity ---

test('can create entity with valid data', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
        'jurisdiction_id' => $jurisdiction->id,
    ]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->set('user_profile_id', $profile->id)
        ->set('name', 'My LLC')
        ->set('entity_type', EntityType::LLC->value)
        ->set('tax_id', '12-3456789')
        ->call('create')
        ->assertHasNoErrors();

    expect(Entity::query()->where('name', 'My LLC')->exists())->toBeTrue();
});

test('cannot create entity without required fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->set('user_profile_id', '')
        ->set('name', '')
        ->set('entity_type', '')
        ->set('tax_id', '')
        ->call('create')
        ->assertHasErrors(['user_profile_id', 'name', 'entity_type', 'tax_id']);
});

test('cannot create entity with invalid entity type', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->set('user_profile_id', $profile->id)
        ->set('name', 'My Company')
        ->set('entity_type', 'InvalidType')
        ->set('tax_id', '12-3456789')
        ->call('create')
        ->assertHasErrors(['entity_type']);
});

test('cannot create entity for another user profile', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProfile = UserProfile::factory()->create(['user_id' => $otherUser->id]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->set('user_profile_id', $otherProfile->id)
        ->set('name', 'My LLC')
        ->set('entity_type', EntityType::LLC->value)
        ->set('tax_id', '12-3456789')
        ->call('create')
        ->assertForbidden();
});

// --- Update Entity ---

test('can update entity with valid data', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $entity = Entity::factory()->create([
        'user_profile_id' => $profile->id,
        'name' => 'Old Name',
    ]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->call('edit', $entity->id)
        ->set('name', 'New Name')
        ->call('update')
        ->assertHasNoErrors();

    expect($entity->fresh()->name)->toBe('New Name');
});

test('can assign address to entity', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->call('edit', $entity->id)
        ->set('address_id', (string) $address->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($entity->fresh()->address_id)->toBe($address->id);
});

test('other user cannot update entity', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);
    $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

    Livewire::actingAs($otherUser)
        ->test(Entities::class)
        ->call('edit', $entity->id)
        ->assertForbidden();
});

// --- Cancel Edit ---

test('can cancel editing an entity', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->call('edit', $entity->id)
        ->assertSet('editingId', $entity->id)
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertSet('name', '');
});

// --- Delete Entity ---

test('can delete entity', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

    Livewire::actingAs($user)
        ->test(Entities::class)
        ->call('delete', $entity->id)
        ->assertHasNoErrors();

    expect(Entity::query()->find($entity->id))->toBeNull();
});

test('other user cannot delete entity', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);
    $entity = Entity::factory()->create(['user_profile_id' => $profile->id]);

    Livewire::actingAs($otherUser)
        ->test(Entities::class)
        ->call('delete', $entity->id)
        ->assertForbidden();
});
