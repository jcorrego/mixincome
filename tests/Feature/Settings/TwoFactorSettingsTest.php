<?php

declare(strict_types=1);

use App\Livewire\Settings\TwoFactor;
use App\Models\User;
use Livewire\Livewire;

test('2fa settings displays status', function (): void {
    $user = User::factory()->create()->refresh();

    // Confirm password (required to access 2FA settings)
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    Livewire::actingAs($user)
        ->test(TwoFactor::class)
        ->assertSet('twoFactorEnabled', false);
});

test('recovery codes can be regenerated', function (): void {
    $user = User::factory()->create();

    // Confirm password and enable 2FA first
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication');

    $user = $user->fresh();
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    $oldCodes = $user->two_factor_recovery_codes;

    $this->actingAs($user)
        ->post('/user/two-factor-recovery-codes');

    expect($user->fresh()->two_factor_recovery_codes)->not->toBe($oldCodes);
});
