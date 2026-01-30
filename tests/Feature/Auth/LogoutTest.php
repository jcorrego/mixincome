<?php

declare(strict_types=1);

use App\Models\User;

test('users can logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
