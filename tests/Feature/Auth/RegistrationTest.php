<?php

declare(strict_types=1);

use App\Models\User;

test('registration page renders', function (): void {
    $this->get('/register')->assertOk();
});

test('users can register', function (): void {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('registration requires valid email', function (): void {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});

test('registration requires password confirmation', function (): void {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');
});

test('registration rejects duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});
