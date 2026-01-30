<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('verify email page renders for unverified user', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertOk();
});

test('email can be verified', function (): void {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('verified user can access protected routes', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

test('unverified user redirected from protected routes', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});
