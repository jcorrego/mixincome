<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages User Tax Profiles (UserProfile CRUD operations).
 *
 * Handles API operations for creating, reading, updating, and deleting user tax profiles
 * with proper authorization checks and eager loading of relationships.
 */
final class UserProfileController
{
    /**
     * List all user profiles with eager-loaded relationships.
     *
     * @param  Request  $request  The HTTP request
     * @return JsonResponse Array of user profiles with jurisdiction, address, and entities
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserProfile::class);

        $profiles = auth()->user()->userProfiles()
            ->with(['jurisdiction', 'address', 'entities'])
            ->get();

        return response()->json($profiles);
    }

    /**
     * Create a new user profile.
     *
     * @param  StoreUserProfileRequest  $request  Validated request with jurisdiction_id, tax_id, address_id
     * @return JsonResponse Created profile with 201 status code
     */
    public function store(StoreUserProfileRequest $request): JsonResponse
    {
        $this->authorize('create', UserProfile::class);

        $profile = auth()->user()->userProfiles()->create($request->validated());

        return response()->json($profile->load(['jurisdiction', 'address', 'entities']), 201);
    }

    /**
     * Get a specific user profile by ID.
     *
     * @param  UserProfile  $userProfile  The user profile to retrieve (route model binding)
     * @return JsonResponse The profile with relationships
     */
    public function show(UserProfile $userProfile): JsonResponse
    {
        $this->authorize('view', $userProfile);

        return response()->json($userProfile->load(['jurisdiction', 'address', 'entities']));
    }

    /**
     * Update an existing user profile.
     *
     * @param  UpdateUserProfileRequest  $request  Validated request with updated data
     * @param  UserProfile  $userProfile  The profile to update (route model binding)
     * @return JsonResponse Updated profile with relationships
     */
    public function update(UpdateUserProfileRequest $request, UserProfile $userProfile): JsonResponse
    {
        $this->authorize('update', $userProfile);

        $userProfile->update($request->validated());

        return response()->json($userProfile->load(['jurisdiction', 'address', 'entities']));
    }

    /**
     * Delete a user profile.
     *
     * Requires that the profile has no associated entities (enforced by policy).
     *
     * @param  UserProfile  $userProfile  The profile to delete (route model binding)
     * @return JsonResponse 204 No Content response
     */
    public function destroy(UserProfile $userProfile): JsonResponse
    {
        $this->authorize('delete', $userProfile);

        $userProfile->delete();

        return response()->json(null, 204);
    }
}
