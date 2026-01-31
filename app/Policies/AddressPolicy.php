<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

/**
 * Authorization policy for Address model.
 *
 * Enforces ownership-based access control: users can only view, update, and delete
 * their own addresses. Deletion is also prevented if the address is in use by
 * any UserProfile or Entity.
 */
final class AddressPolicy
{
    /**
     * Allow all authenticated users to view any address (list).
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow viewing an address only if the user owns it.
     *
     * @param  User  $user  The authenticated user
     * @param  Address  $address  The address to view
     * @return bool True if user owns the address
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Allow all authenticated users to create addresses.
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Allow updating an address only if the user owns it.
     *
     * @param  User  $user  The authenticated user
     * @param  Address  $address  The address to update
     * @return bool True if user owns the address
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Allow deleting an address only if the user owns it and it is not in use.
     *
     * Prevents deletion of addresses currently associated with UserProfiles or Entities.
     *
     * @param  User  $user  The authenticated user
     * @param  Address  $address  The address to delete
     * @return bool True if user owns address and it is not in use
     */
    public function delete(User $user, Address $address): bool
    {
        if ($user->id !== $address->user_id) {
            return false;
        }

        // Prevent deletion if address is in use by any model
        $inUse = $address->userProfiles()->exists()
            || $address->entities()->exists();

        return ! $inUse;
    }
}
