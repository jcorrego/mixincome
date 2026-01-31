<?php

declare(strict_types=1);

use App\Livewire\Management\UserProfiles;
use App\Models\Address;
use App\Models\Entity;
use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;
use Livewire\Livewire;

// --- View Profiles ---

test('authenticated user can view profiles page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/management/profiles')
        ->assertOk()
        ->assertSeeLivewire(UserProfiles::class);
});

test('unauthenticated user is redirected to login from profiles', function (): void {
    $this->get('/management/profiles')
        ->assertRedirect('/login');
});

// --- Create Profile ---

test('can create profile with valid data', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->set('jurisdiction_id', $jurisdiction->id)
        ->set('tax_id', 'NIF123456789')
        ->call('create')
        ->assertHasNoErrors();

    expect(UserProfile::query()->where('user_id', $user->id)->where('jurisdiction_id', $jurisdiction->id)->exists())->toBeTrue();
});

test('cannot create profile without required fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->set('jurisdiction_id', '')
        ->set('tax_id', '')
        ->call('create')
        ->assertHasErrors(['jurisdiction_id', 'tax_id']);
});

test('cannot create profile with invalid jurisdiction', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->set('jurisdiction_id', 9999)
        ->set('tax_id', 'NIF123456789')
        ->call('create')
        ->assertHasErrors(['jurisdiction_id']);
});

test('cannot create duplicate profile for same jurisdiction', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    UserProfile::factory()->create([
        'user_id' => $user->id,
        'jurisdiction_id' => $jurisdiction->id,
        'tax_id' => 'NIF123456789',
    ]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->set('jurisdiction_id', $jurisdiction->id)
        ->set('tax_id', 'DIFFERENT-TAX-ID')
        ->call('create')
        ->assertHasErrors(['jurisdiction_id']);
});

// --- Update Profile ---

test('can update profile with valid data', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
        'jurisdiction_id' => $jurisdiction->id,
        'tax_id' => 'OLD-TAX-ID',
    ]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('edit', $profile->id)
        ->set('tax_id', 'NEW-TAX-ID')
        ->call('update')
        ->assertHasNoErrors();

    expect($profile->fresh()->tax_id)->toBe('NEW-TAX-ID');
});

test('can assign address to profile', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
        'jurisdiction_id' => $jurisdiction->id,
    ]);
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('edit', $profile->id)
        ->set('address_id', (string) $address->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($profile->fresh()->address_id)->toBe($address->id);
});

test('other user cannot update profile', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($otherUser)
        ->test(UserProfiles::class)
        ->call('edit', $profile->id)
        ->assertForbidden();
});

// --- Cancel Edit ---

test('can cancel editing a profile', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('edit', $profile->id)
        ->assertSet('editingId', $profile->id)
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertSet('tax_id', '');
});

// --- Delete Profile ---

test('can delete profile without entities', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('delete', $profile->id)
        ->assertHasNoErrors();

    expect(UserProfile::query()->find($profile->id))->toBeNull();
});

test('cannot delete profile with entities', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    Entity::factory()->create(['user_profile_id' => $profile->id]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('delete', $profile->id)
        ->assertForbidden();

    expect(UserProfile::query()->find($profile->id))->not->toBeNull();
});

test('other user cannot delete profile', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($otherUser)
        ->test(UserProfiles::class)
        ->call('delete', $profile->id)
        ->assertForbidden();
});
