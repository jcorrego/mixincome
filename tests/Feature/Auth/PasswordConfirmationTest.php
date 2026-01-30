<?php

declare(strict_types=1);

use App\Models\User;

test('confirm password page renders', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/user/confirm-password')
        ->assertOk();
});

test('password can be confirmed', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/user/confirm-password', [
            'password' => 'password',
        ])->assertRedirect();
});

test('wrong password not confirmed', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/user/confirm-password', [
            'password' => 'wrong-password',
        ])->assertSessionHasErrors();
});
