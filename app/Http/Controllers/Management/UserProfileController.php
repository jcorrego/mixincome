<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserProfileController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserProfile::class);

        $profiles = auth()->user()->userProfiles()
            ->with(['jurisdiction', 'address', 'entities'])
            ->get();

        return response()->json($profiles);
    }

    public function store(StoreUserProfileRequest $request): JsonResponse
    {
        $this->authorize('create', UserProfile::class);

        $profile = auth()->user()->userProfiles()->create($request->validated());

        return response()->json($profile->load(['jurisdiction', 'address', 'entities']), 201);
    }

    public function show(UserProfile $userProfile): JsonResponse
    {
        $this->authorize('view', $userProfile);

        return response()->json($userProfile->load(['jurisdiction', 'address', 'entities']));
    }

    public function update(UpdateUserProfileRequest $request, UserProfile $userProfile): JsonResponse
    {
        $this->authorize('update', $userProfile);

        $userProfile->update($request->validated());

        return response()->json($userProfile->load(['jurisdiction', 'address', 'entities']));
    }

    public function destroy(UserProfile $userProfile): JsonResponse
    {
        $this->authorize('delete', $userProfile);

        $userProfile->delete();

        return response()->json(null, 204);
    }
}
