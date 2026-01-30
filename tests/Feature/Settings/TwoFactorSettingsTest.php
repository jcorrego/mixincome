<?php

declare(strict_types=1);

use App\Livewire\Settings\TwoFactor;
use App\Livewire\Settings\TwoFactor\RecoveryCodes;
use App\Models\User;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function (): void {
    $this->user = User::factory()->create()->refresh();

    // Confirm password (required for 2FA operations)
    $this->actingAs($this->user)
        ->post('/user/confirm-password', ['password' => 'password']);
});

test('2fa settings displays status', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->assertSet('twoFactorEnabled', false);
});

test('2fa can be enabled', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->call('enable')
        ->assertSet('showModal', true)
        ->assertNotSet('qrCodeSvg', '');
});

test('2fa shows verification step', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->call('enable')
        ->call('showVerificationIfNecessary')
        ->assertSet('showVerificationStep', true);
});

test('2fa confirm with valid code', function (): void {
    // Enable 2FA via Livewire component
    $component = Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->call('enable');

    $this->user->refresh();

    // Get a valid OTP code via Google2FA
    $secret = decrypt($this->user->two_factor_secret);
    $google2fa = new Google2FA();
    $code = $google2fa->getCurrentOtp($secret);

    $component
        ->set('code', $code)
        ->call('confirmTwoFactor')
        ->assertSet('twoFactorEnabled', true)
        ->assertSet('showModal', false);
});

test('2fa can be disabled', function (): void {
    // Enable and confirm
    $this->actingAs($this->user)
        ->post('/user/two-factor-authentication');

    $this->user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::actingAs($this->user->refresh())
        ->test(TwoFactor::class)
        ->assertSet('twoFactorEnabled', true)
        ->call('disable')
        ->assertSet('twoFactorEnabled', false);
});

test('2fa modal can be closed', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->call('enable')
        ->assertSet('showModal', true)
        ->call('closeModal')
        ->assertSet('showModal', false)
        ->assertSet('qrCodeSvg', '')
        ->assertSet('manualSetupKey', '');
});

test('2fa reset verification clears state', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwoFactor::class)
        ->call('enable')
        ->call('showVerificationIfNecessary')
        ->set('code', '123456')
        ->call('resetVerification')
        ->assertSet('code', '')
        ->assertSet('showVerificationStep', false);
});

test('recovery codes load for enabled 2fa', function (): void {
    $this->actingAs($this->user)
        ->post('/user/two-factor-authentication');

    $this->user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::actingAs($this->user->refresh())
        ->test(RecoveryCodes::class)
        ->assertNotSet('recoveryCodes', []);
});

test('recovery codes can be regenerated via livewire', function (): void {
    $this->actingAs($this->user)
        ->post('/user/two-factor-authentication');

    $this->user->forceFill(['two_factor_confirmed_at' => now()])->save();

    $component = Livewire::actingAs($this->user->refresh())
        ->test(RecoveryCodes::class);

    $oldCodes = $component->get('recoveryCodes');

    $component->call('regenerateRecoveryCodes');

    expect($component->get('recoveryCodes'))->not->toBe($oldCodes);
});

test('recovery codes can be regenerated via http', function (): void {
    $this->actingAs($this->user)
        ->post('/user/two-factor-authentication');

    $this->user->forceFill(['two_factor_confirmed_at' => now()])->save();
    $this->user->refresh();

    $oldCodes = $this->user->two_factor_recovery_codes;

    $this->actingAs($this->user)
        ->post('/user/two-factor-recovery-codes');

    expect($this->user->fresh()->two_factor_recovery_codes)->not->toBe($oldCodes);
});
