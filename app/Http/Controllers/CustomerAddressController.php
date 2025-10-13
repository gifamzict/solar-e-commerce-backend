<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerAddressController extends Controller
{
    /**
     * Get all saved addresses for the authenticated user
     * GET /api/customer/addresses
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $addresses = CustomerAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => (string) $address->id,
                    'label' => $address->label,
                    'first_name' => $address->first_name,
                    'last_name' => $address->last_name,
                    'email' => $address->email,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city,
                    'state' => $address->state,
                    'is_default' => $address->is_default,
                    'created_at' => $address->created_at->toISOString(),
                    'full_name' => $address->full_name,
                    'formatted_address' => $address->formatted_address,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $addresses,
            'message' => 'Addresses retrieved successfully'
        ]);
    }

    /**
     * Store a new address for the authenticated user
     * POST /api/customer/addresses
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'label' => 'nullable|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'is_default' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Add the user_id to the validated data
        $validated['user_id'] = $user->id;

        // If this is set as default or user has no addresses, make it default
        $userAddressCount = CustomerAddress::where('user_id', $user->id)->count();
        if ($userAddressCount === 0 || ($validated['is_default'] ?? false)) {
            $validated['is_default'] = true;
        }

        $address = CustomerAddress::create($validated);

        // If set as default, unset other defaults
        if ($address->is_default) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'email' => $address->email,
                'phone' => $address->phone,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'is_default' => $address->is_default,
                'created_at' => $address->created_at->toISOString(),
                'full_name' => $address->full_name,
                'formatted_address' => $address->formatted_address,
            ],
            'message' => 'Address saved successfully'
        ], 201);
    }

    /**
     * Get a specific address
     * GET /api/customer/addresses/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $address = CustomerAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'email' => $address->email,
                'phone' => $address->phone,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'is_default' => $address->is_default,
                'created_at' => $address->created_at->toISOString(),
                'full_name' => $address->full_name,
                'formatted_address' => $address->formatted_address,
            ],
            'message' => 'Address retrieved successfully'
        ]);
    }

    /**
     * Update an existing address
     * PUT/PATCH /api/customer/addresses/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $address = CustomerAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'label' => 'nullable|string|max:255',
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'phone' => 'sometimes|required|string|max:20',
                'address' => 'sometimes|required|string|max:500',
                'city' => 'sometimes|required|string|max:255',
                'state' => 'sometimes|required|string|max:255',
                'is_default' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $address->update($validated);

        // If set as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $address->id,
                'label' => $address->label,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'email' => $address->email,
                'phone' => $address->phone,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'is_default' => $address->is_default,
                'created_at' => $address->created_at->toISOString(),
                'full_name' => $address->full_name,
                'formatted_address' => $address->formatted_address,
            ],
            'message' => 'Address updated successfully'
        ]);
    }

    /**
     * Delete an address
     * DELETE /api/customer/addresses/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $address = CustomerAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        // If deleting the default address, set another address as default
        if ($address->is_default) {
            $newDefault = CustomerAddress::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->first();
                
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set an address as default
     * POST /api/customer/addresses/{id}/set-default
     */
    public function setDefault(string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $address = CustomerAddress::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Address set as default successfully'
        ]);
    }
}
