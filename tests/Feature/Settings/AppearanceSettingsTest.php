<?php

declare(strict_types=1);

use App\Models\User;

test('appearance settings page renders', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/settings/appearance')
        ->assertOk();
});
