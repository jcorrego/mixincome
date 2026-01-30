<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function (): void {
    if (! Features::enabled(Features::twoFactorAuthentication())) {
        $this->markTestSkipped('2FA not enabled.');
    }
});

test('two factor challenge page redirects without active 2fa session', function (): void {
    // Without an active 2FA login session, it redirects to login
    $this->get('/two-factor-challenge')->assertRedirect('/login');
});

test('two factor can be enabled', function (): void {
    $user = User::factory()->create();

    // Confirm password first (required by Fortify confirmPassword option)
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication')
        ->assertRedirect();

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

test('login with 2fa requires code', function (): void {
    $user = User::factory()->create();

    // Enable and confirm 2FA
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication');

    $user = $user->fresh();
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    // Logout and try login
    auth()->logout();
    session()->flush();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/two-factor-challenge');
});

test('valid recovery code completes login', function (): void {
    $user = User::factory()->create();

    // Enable and confirm 2FA
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication');

    $user = $user->fresh();
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    $recoveryCodes = json_decode((string) decrypt($user->two_factor_recovery_codes), true);

    // Logout and login to trigger 2FA
    auth()->logout();
    session()->flush();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post('/two-factor-challenge', [
        'recovery_code' => $recoveryCodes[0],
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('invalid 2fa code does not authenticate', function (): void {
    $user = User::factory()->create();

    // Enable and confirm 2FA
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication');

    $user = $user->fresh();
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    // Logout and login to trigger 2FA
    auth()->logout();
    session()->flush();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post('/two-factor-challenge', [
        'code' => '000000',
    ])->assertRedirect('/two-factor-challenge');

    $this->assertGuest();
});
