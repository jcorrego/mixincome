<?php

declare(strict_types=1);

use App\Livewire\Management\Addresses;
use App\Models\Address;
use App\Models\Entity;
use App\Models\User;
use App\Models\UserProfile;
use Livewire\Livewire;

// --- View Addresses ---

test('authenticated user can view addresses page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/management/addresses')
        ->assertOk()
        ->assertSeeLivewire(Addresses::class);
});

test('unauthenticated user is redirected to login from addresses', function (): void {
    $this->get('/management/addresses')
        ->assertRedirect('/login');
});

// --- Create Address ---

test('can create address with valid data', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('street', '123 Main St')
        ->set('city', 'Miami')
        ->set('state', 'Florida')
        ->set('postal_code', '33101')
        ->set('country', 'US')
        ->call('create')
        ->assertHasNoErrors();

    expect(Address::query()->where('user_id', $user->id)->where('street', '123 Main St')->exists())->toBeTrue();
});

test('cannot create address without required fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('street', '')
        ->set('city', '')
        ->set('state', '')
        ->set('postal_code', '')
        ->set('country', '')
        ->call('create')
        ->assertHasErrors(['street', 'city', 'state', 'postal_code', 'country']);
});

// --- Update Address ---

test('can update address with valid data', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'street' => 'Old Street',
    ]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('edit', $address->id)
        ->set('street', 'New Street')
        ->call('update')
        ->assertHasNoErrors();

    expect($address->fresh()->street)->toBe('New Street');
});

test('other user cannot update address', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($otherUser)
        ->test(Addresses::class)
        ->call('edit', $address->id)
        ->assertForbidden();
});

// --- Cancel Edit ---

test('can cancel editing an address', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('edit', $address->id)
        ->assertSet('editingId', $address->id)
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertSet('street', '');
});

// --- Delete Address ---

test('can delete address not in use', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('delete', $address->id)
        ->assertHasNoErrors();

    expect(Address::query()->find($address->id))->toBeNull();
});

test('cannot delete address linked to a profile', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    UserProfile::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('delete', $address->id)
        ->assertForbidden();

    expect(Address::query()->find($address->id))->not->toBeNull();
});

test('cannot delete address linked to an entity', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    Entity::factory()->create([
        'user_profile_id' => $profile->id,
        'address_id' => $address->id,
    ]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('delete', $address->id)
        ->assertForbidden();

    expect(Address::query()->find($address->id))->not->toBeNull();
});

test('other user cannot delete address', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($otherUser)
        ->test(Addresses::class)
        ->call('delete', $address->id)
        ->assertForbidden();
});

// --- API Endpoint Tests (for Controller Coverage) ---

test('api index returns user addresses', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson('/api/management/addresses')
        ->assertOk()
        ->assertJsonCount(1);
});

test('api store creates address', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/management/addresses', [
            'street' => '456 Oak Ave',
            'city' => 'Atlanta',
            'state' => 'Georgia',
            'postal_code' => '30303',
            'country' => 'US',
        ])
        ->assertCreated();
});

test('api show returns address', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson("/api/management/addresses/{$address->id}")
        ->assertOk();
});

test('api update modifies address', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("/api/management/addresses/{$address->id}", [
            'street' => 'Updated Street',
            'city' => 'Updated City',
            'state' => 'Updated State',
            'postal_code' => '99999',
            'country' => 'US',
        ])
        ->assertOk();
});

test('api destroy deletes address', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson("/api/management/addresses/{$address->id}")
        ->assertNoContent();
});

test('api unauthorized user cannot update address', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)
        ->patchJson("/api/management/addresses/{$address->id}", [
            'street' => 'Hacked Street',
            'city' => 'Hacked City',
            'state' => 'Hacked State',
            'postal_code' => '66666',
            'country' => 'US',
        ])
        ->assertForbidden();
});

test('api unauthorized user cannot destroy address', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)
        ->deleteJson("/api/management/addresses/{$address->id}")
        ->assertForbidden();
});
