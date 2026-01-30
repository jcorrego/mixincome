<?php

declare(strict_types=1);

use App\Livewire\Settings\Password;
use App\Models\User;
use Livewire\Livewire;

test('password settings page renders', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/settings/password')
        ->assertOk();
});

test('password can be changed', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Password::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertDispatched('password-updated');
});

test('wrong current password rejected', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Password::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors('current_password');
});
