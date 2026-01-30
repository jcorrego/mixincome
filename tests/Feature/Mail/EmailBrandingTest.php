<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;

test('verification email contains mixincome logo', function (): void {
    $user = User::factory()->unverified()->create();

    $notification = new VerifyEmail();
    $html = (string) $notification->toMail($user)->render();

    expect($html)->toContain('MixIncome')
        ->and($html)->toContain('<svg');
});

test('verification email contains branded footer', function (): void {
    $user = User::factory()->unverified()->create();

    $notification = new VerifyEmail();
    $html = (string) $notification->toMail($user)->render();

    expect($html)->toContain('MixIncome')
        ->and($html)->toContain('©')
        ->and($html)->toContain(date('Y'));
});

test('verification email has branded button color', function (): void {
    $user = User::factory()->unverified()->create();

    $notification = new VerifyEmail();
    $html = (string) $notification->toMail($user)->render();

    expect($html)->toContain('#1e293b');
});

test('password reset email contains mixincome branding', function (): void {
    $user = User::factory()->create();

    $notification = new ResetPassword('fake-token');
    $html = (string) $notification->toMail($user)->render();

    expect($html)->toContain('MixIncome')
        ->and($html)->toContain('©')
        ->and($html)->toContain('#1e293b');
});
