<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages legal entities (Entity CRUD operations).
 *
 * Handles API operations for creating, reading, updating, and deleting legal entities
 * with proper authorization checks and eager loading of relationships.
 */
final class EntityController
{
    /**
     * List all entities belonging to the authenticated user's profiles.
     *
     * Filters entities by user_profile.user_id to ensure user isolation.
     *
     * @param  Request  $request  The HTTP request
     * @return JsonResponse Array of entities with userProfile and address relationships
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Entity::class);

        $entities = Entity::query()
            ->whereHas('userProfile', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['userProfile', 'address'])
            ->get();

        return response()->json($entities);
    }

    /**
     * Create a new legal entity.
     *
     * @param  StoreEntityRequest  $request  Validated request with user_profile_id, name, entity_type, tax_id
     * @return JsonResponse Created entity with 201 status code and relationships
     */
    public function store(StoreEntityRequest $request): JsonResponse
    {
        $this->authorize('create', Entity::class);

        $entity = Entity::create($request->validated());

        return response()->json($entity->load(['userProfile', 'address']), 201);
    }

    /**
     * Get a specific entity by ID.
     *
     * @param  Entity  $entity  The entity to retrieve (route model binding)
     * @return JsonResponse The entity with relationships
     */
    public function show(Entity $entity): JsonResponse
    {
        $this->authorize('view', $entity);

        return response()->json($entity->load(['userProfile', 'address']));
    }

    /**
     * Update an existing entity.
     *
     * @param  UpdateEntityRequest  $request  Validated request with updated data
     * @param  Entity  $entity  The entity to update (route model binding)
     * @return JsonResponse Updated entity with relationships
     */
    public function update(UpdateEntityRequest $request, Entity $entity): JsonResponse
    {
        $this->authorize('update', $entity);

        $entity->update($request->validated());

        return response()->json($entity->load(['userProfile', 'address']));
    }

    /**
     * Delete an entity.
     *
     * Requires that the entity has no associated accounts or transactions (enforced by policy).
     *
     * @param  Entity  $entity  The entity to delete (route model binding)
     * @return JsonResponse 204 No Content response
     */
    public function destroy(Entity $entity): JsonResponse
    {
        $this->authorize('delete', $entity);

        $entity->delete();

        return response()->json(null, 204);
    }
}
