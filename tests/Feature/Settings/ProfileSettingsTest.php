<?php

declare(strict_types=1);

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile settings page renders', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/settings/profile')
        ->assertOk();
});

test('profile name can be updated', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Updated Name')
        ->call('updateProfileInformation')
        ->assertDispatched('profile-updated');

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('profile email can be updated', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('email', 'newemail@example.com')
        ->call('updateProfileInformation');

    $user = $user->fresh();
    expect($user->email)->toBe('newemail@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('profile settings requires auth', function (): void {
    $this->get('/settings/profile')
        ->assertRedirect('/login');
});
