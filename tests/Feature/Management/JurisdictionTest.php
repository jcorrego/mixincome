<?php

declare(strict_types=1);

use App\Livewire\Management\Jurisdictions;
use App\Models\Jurisdiction;
use App\Models\User;
use Livewire\Livewire;

// --- View Jurisdictions ---

test('authenticated user can view jurisdictions page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/management/jurisdictions')
        ->assertOk()
        ->assertSeeLivewire(Jurisdictions::class);
});

test('unauthenticated user is redirected to login', function (): void {
    $this->get('/management/jurisdictions')
        ->assertRedirect('/login');
});

// --- Create Jurisdiction ---

test('can create jurisdiction with valid data', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->set('name', 'Germany')
        ->set('iso_code', 'DE')
        ->set('timezone', 'Europe/Berlin')
        ->set('default_currency', 'EUR')
        ->call('create')
        ->assertHasNoErrors();

    expect(Jurisdiction::query()->where('iso_code', 'DE')->exists())->toBeTrue();
});

test('cannot create jurisdiction with duplicate iso_code', function (): void {
    $user = User::factory()->create();
    Jurisdiction::factory()->create(['iso_code' => 'DE']);

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->set('name', 'Germany')
        ->set('iso_code', 'DE')
        ->set('timezone', 'Europe/Berlin')
        ->set('default_currency', 'EUR')
        ->call('create')
        ->assertHasErrors(['iso_code']);
});

test('cannot create jurisdiction with invalid iso_code length', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->set('name', 'Germany')
        ->set('iso_code', 'DEDE')
        ->set('timezone', 'Europe/Berlin')
        ->set('default_currency', 'EUR')
        ->call('create')
        ->assertHasErrors(['iso_code']);
});

test('cannot create jurisdiction with missing required fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->set('name', '')
        ->set('iso_code', '')
        ->set('timezone', '')
        ->set('default_currency', '')
        ->call('create')
        ->assertHasErrors(['name', 'iso_code', 'timezone', 'default_currency']);
});

// --- Update Jurisdiction ---

test('can update jurisdiction with valid data', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create(['name' => 'United States']);

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->call('edit', $jurisdiction->id)
        ->set('name', 'USA')
        ->set('iso_code', $jurisdiction->iso_code)
        ->set('timezone', $jurisdiction->timezone)
        ->set('default_currency', $jurisdiction->default_currency)
        ->call('update')
        ->assertHasNoErrors();

    expect($jurisdiction->fresh()->name)->toBe('USA');
});

test('cannot update jurisdiction iso_code to duplicate value', function (): void {
    $user = User::factory()->create();
    Jurisdiction::factory()->create(['iso_code' => 'ES']);
    $jurisdiction = Jurisdiction::factory()->create(['iso_code' => 'US']);

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->call('edit', $jurisdiction->id)
        ->set('iso_code', 'ES')
        ->call('update')
        ->assertHasErrors(['iso_code']);
});

test('cannot update jurisdiction with invalid timezone', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->call('edit', $jurisdiction->id)
        ->set('timezone', 'Invalid/Timezone')
        ->call('update')
        ->assertHasErrors(['timezone']);
});

// --- Cancel Edit ---

test('can cancel editing a jurisdiction', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->call('edit', $jurisdiction->id)
        ->assertSet('editingId', $jurisdiction->id)
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertSet('name', '');
});

// --- Delete Jurisdiction ---

test('can delete jurisdiction with no dependencies', function (): void {
    $user = User::factory()->create();
    $jurisdiction = Jurisdiction::factory()->create();

    Livewire::actingAs($user)
        ->test(Jurisdictions::class)
        ->call('delete', $jurisdiction->id)
        ->assertHasNoErrors();

    expect(Jurisdiction::query()->find($jurisdiction->id))->toBeNull();
});
