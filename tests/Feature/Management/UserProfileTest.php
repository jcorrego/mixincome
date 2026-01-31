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

test('can create profile with address', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->set('jurisdiction_id', $jurisdiction->id)
        ->set('tax_id', 'NIF123456789')
        ->set('address_id', (string) $address->id)
        ->call('create')
        ->assertHasNoErrors();

    expect(UserProfile::query()->where('user_id', $user->id)->where('address_id', $address->id)->exists())->toBeTrue();
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

// --- Address Dropdown Display Format ---

test('profile create form shows addresses with country in dropdown', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'street' => '789 Gran Via',
        'city' => 'Madrid',
        'country' => 'ES',
    ]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->assertSee('789 Gran Via, Madrid (ES)');
});

test('profile edit form shows addresses with country in dropdown', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'street' => '321 Elm St',
        'city' => 'Denver',
        'country' => 'US',
    ]);
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);

    Livewire::actingAs($user)
        ->test(UserProfiles::class)
        ->call('edit', $profile->id)
        ->assertSee('321 Elm St, Denver (US)');
});

// --- API Endpoint Tests (for Controller Coverage) ---

test('api index returns user profiles', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson('/api/management/user-profiles')
        ->assertOk()
        ->assertJsonCount(1);
});

test('api store creates profile', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/management/user-profiles', [
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'API-TAX-ID',
        ])
        ->assertCreated();
});

test('api show returns profile', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson("/api/management/user-profiles/{$profile->id}")
        ->assertOk();
});

test('api update modifies profile', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $jurisdiction = Jurisdiction::factory()->create();

    $this->actingAs($user)
        ->patchJson("/api/management/user-profiles/{$profile->id}", [
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'UPDATED-TAX',
        ])
        ->assertOk();
});

test('api destroy deletes profile', function (): void {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson("/api/management/user-profiles/{$profile->id}")
        ->assertNoContent();
});

test('api unauthorized user cannot update profile', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);
    $jurisdiction = Jurisdiction::factory()->create();

    $this->actingAs($otherUser)
        ->patchJson("/api/management/user-profiles/{$profile->id}", [
            'jurisdiction_id' => $jurisdiction->id,
            'tax_id' => 'HACKER-TAX',
        ])
        ->assertForbidden();
});

test('api unauthorized user cannot destroy profile', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)
        ->deleteJson("/api/management/user-profiles/{$profile->id}")
        ->assertForbidden();
});
