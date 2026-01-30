<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Profile extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = $this->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = $this->user();

        /** @var array{name: string, email: string} $validated */
        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = $this->user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return ! $this->user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return $this->user()->hasVerifiedEmail();
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
