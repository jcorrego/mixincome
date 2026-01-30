<?php

declare(strict_types=1);

use App\Models\User;

test('dashboard renders for authenticated users', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');
});

test('dashboard requires authentication', function (): void {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

test('dashboard requires verified email', function (): void {
    $this->actingAs(User::factory()->unverified()->create())
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});
