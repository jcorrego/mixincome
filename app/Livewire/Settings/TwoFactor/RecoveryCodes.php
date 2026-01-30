<?php

declare(strict_types=1);

namespace App\Livewire\Settings\TwoFactor;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class RecoveryCodes extends Component
{
    /** @var list<string> */
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes($this->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = $this->user();

        if ($user->hasEnabledTwoFactorAuthentication() && is_string($user->two_factor_recovery_codes)) {
            try {
                /** @var string $decrypted */
                $decrypted = decrypt($user->two_factor_recovery_codes);
                /** @var list<string> $codes */
                $codes = json_decode($decrypted, true);
                $this->recoveryCodes = $codes;
            } catch (Exception) { // @codeCoverageIgnoreStart
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            } // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get the authenticated user.
     */
    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }
}
