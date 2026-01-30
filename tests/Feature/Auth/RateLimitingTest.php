<?php

declare(strict_types=1);

use App\Models\User;

test('login rate limited after 5 attempts', function (): void {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('2fa rate limited after 5 attempts', function (): void {
    $user = User::factory()->create();

    // Enable and confirm 2FA
    $this->actingAs($user)
        ->post('/user/confirm-password', ['password' => 'password']);

    $this->actingAs($user)
        ->post('/user/two-factor-authentication');

    $user = $user->fresh();
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    // Logout and start 2FA login flow
    auth()->logout();
    session()->flush();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/two-factor-challenge', [
            'code' => '000000',
        ]);
    }

    $this->post('/two-factor-challenge', [
        'code' => '000000',
    ])->assertStatus(429);
});
