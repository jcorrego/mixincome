<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EntityController
{
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

    public function store(StoreEntityRequest $request): JsonResponse
    {
        $this->authorize('create', Entity::class);

        $entity = Entity::create($request->validated());

        return response()->json($entity->load(['userProfile', 'address']), 201);
    }

    public function show(Entity $entity): JsonResponse
    {
        $this->authorize('view', $entity);

        return response()->json($entity->load(['userProfile', 'address']));
    }

    public function update(UpdateEntityRequest $request, Entity $entity): JsonResponse
    {
        $this->authorize('update', $entity);

        $entity->update($request->validated());

        return response()->json($entity->load(['userProfile', 'address']));
    }

    public function destroy(Entity $entity): JsonResponse
    {
        $this->authorize('delete', $entity);

        $entity->delete();

        return response()->json(null, 204);
    }
}
