<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('profile can be updated via action', function (): void {
    $user = User::factory()->create();

    $action = new UpdateUserProfileInformation();
    $action->update($user, [
        'name' => 'New Name',
        'email' => $user->email,
    ]);

    expect($user->fresh()->name)->toBe('New Name');
});

test('email change clears verification via action', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $action = new UpdateUserProfileInformation();
    $action->update($user, [
        'name' => $user->name,
        'email' => 'new@example.com',
    ]);

    $user = $user->fresh();
    expect($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});
