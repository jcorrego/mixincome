<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

/**
 * Authorization policy for Entity model.
 *
 * Enforces ownership-based access control: users can only view, update, and delete
 * entities that belong to their profiles (via userProfile.user_id).
 */
final class EntityPolicy
{
    /**
     * Allow all authenticated users to view any entity (list).
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow viewing an entity only if the user owns its profile.
     *
     * @param  User  $user  The authenticated user
     * @param  Entity  $entity  The entity to view
     * @return bool True if user owns the profile that owns the entity
     */
    public function view(User $user, Entity $entity): bool
    {
        return $user->id === $entity->userProfile->user_id;
    }

    /**
     * Allow all authenticated users to create entities.
     *
     * @param  User  $user  The authenticated user
     * @return bool Always true for authenticated users
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Allow updating an entity only if the user owns its profile.
     *
     * @param  User  $user  The authenticated user
     * @param  Entity  $entity  The entity to update
     * @return bool True if user owns the profile that owns the entity
     */
    public function update(User $user, Entity $entity): bool
    {
        return $user->id === $entity->userProfile->user_id;
    }

    /**
     * Allow deleting an entity only if the user owns its profile.
     *
     * Complex deletion logic (checking for accounts/transactions) is deferred to Fase 2.
     *
     * @param  User  $user  The authenticated user
     * @param  Entity  $entity  The entity to delete
     * @return bool True if user owns the profile that owns the entity
     */
    public function delete(User $user, Entity $entity): bool
    {
        if ($user->id !== $entity->userProfile->user_id) {
            return false;
        }

        // Prevent deletion if entity has associated data (deferred to Fase 2 for complex logic)
        // For now, allow deletion
        return true;
    }
}
