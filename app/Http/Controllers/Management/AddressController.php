<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages user addresses (Address CRUD operations).
 *
 * Handles API operations for creating, reading, updating, and deleting addresses
 * with proper authorization checks and owner-based access control.
 */
final class AddressController
{
    /**
     * List all addresses belonging to the authenticated user.
     *
     * @param  Request  $request  The HTTP request
     * @return JsonResponse Array of user addresses
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Address::class);

        $addresses = auth()->user()->addresses()->get();

        return response()->json($addresses);
    }

    /**
     * Create a new address owned by the authenticated user.
     *
     * @param  StoreAddressRequest  $request  Validated request with street, city, state, postal_code, country
     * @return JsonResponse Created address with 201 status code
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $this->authorize('create', Address::class);

        $address = auth()->user()->addresses()->create($request->validated());

        return response()->json($address, 201);
    }

    /**
     * Get a specific address by ID.
     *
     * @param  Address  $address  The address to retrieve (route model binding)
     * @return JsonResponse The address data
     */
    public function show(Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return response()->json($address);
    }

    /**
     * Update an existing address.
     *
     * @param  UpdateAddressRequest  $request  Validated request with updated address fields
     * @param  Address  $address  The address to update (route model binding)
     * @return JsonResponse Updated address
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address->update($request->validated());

        return response()->json($address);
    }

    /**
     * Delete an address.
     *
     * Requires that the address is not in use by any UserProfile or Entity (enforced by policy).
     *
     * @param  Address  $address  The address to delete (route model binding)
     * @return JsonResponse 204 No Content response
     */
    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json(null, 204);
    }
}
