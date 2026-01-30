<?php

declare(strict_types=1);

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
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

test('resend verification for unverified user', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('resendVerificationNotification')
        ->assertHasNoErrors();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('resend verification redirects verified user', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('resendVerificationNotification')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('has unverified email computed property', function (): void {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('hasUnverifiedEmail', true);
});

test('show delete user computed property', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('showDeleteUser', true);
});
