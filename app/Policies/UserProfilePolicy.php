<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

/**
 * Authorization policy for UserProfile model.
 *
 * Enforces ownership-based access control: users can only view, update, and delete
 * their own profiles. Deletion is also prevented if the profile has associated entities.
 */
final class UserProfilePolicy
{
    /**
     * Allow all authenticated users to view any profile (list).
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow viewing a profile only if the user owns it.
     *
     * @param  User  $user  The authenticated user
     * @param  UserProfile  $profile  The profile to view
     * @return bool True if user owns the profile
     */
    public function view(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    /**
     * Allow all authenticated users to create profiles.
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Allow updating a profile only if the user owns it.
     *
     * @param  User  $user  The authenticated user
     * @param  UserProfile  $profile  The profile to update
     * @return bool True if user owns the profile
     */
    public function update(User $user, UserProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    /**
     * Allow deleting a profile only if the user owns it and it has no entities.
     *
     * Prevents cascade deletion of entities when a profile is deleted.
     *
     * @param  User  $user  The authenticated user
     * @param  UserProfile  $profile  The profile to delete
     * @return bool True if user owns profile and it has no entities
     */
    public function delete(User $user, UserProfile $profile): bool
    {
        if ($user->id !== $profile->user_id) {
            return false;
        }

        // Prevent deletion if profile has associated entities
        return $profile->entities()->doesntExist();
    }
}
