<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

final class EntityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Entity $entity): bool
    {
        return $user->id === $entity->userProfile->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Entity $entity): bool
    {
        return $user->id === $entity->userProfile->user_id;
    }

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
