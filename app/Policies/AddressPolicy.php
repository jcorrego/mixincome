<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

final class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

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
