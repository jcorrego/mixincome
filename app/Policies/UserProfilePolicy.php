<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

final class UserProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function delete(User $user, UserProfile $profile): bool
    {
        if ($user->id !== $profile->user_id) {
            return false;
        }

        // Prevent deletion if profile has associated entities
        return $profile->entities()->doesntExist();
    }
}
