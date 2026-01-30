<?php

declare(strict_types=1);

use App\Models\User;

test('navigation shows dashboard link', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertSee('Dashboard');
});

test('navigation shows settings link', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertSee('Settings');
});

test('user menu shows name and email', function (): void {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});

test('user menu has logout option', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertSee('Log Out');
});
