<?php

declare(strict_types=1);

use App\Models\User;

test('login page renders', function (): void {
    $this->get('/login')->assertOk();
});

test('users can login with valid credentials', function (): void {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('users cannot login with invalid credentials', function (): void {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('unauthenticated root shows login', function (): void {
    $this->get('/')->assertOk()->assertSee('Log in');
});

test('authenticated root redirects to dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Dashboard');
});
