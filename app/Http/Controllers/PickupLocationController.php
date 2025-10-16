<?php

namespace App\Http\Controllers;

use App\Models\PickupLocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PickupLocationController extends Controller
{
    /**
     * Display a listing of pickup locations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PickupLocation::query();

        // Filter by active status if specified
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address_line1', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // Sort by default first, then by name
        $locations = $query->orderBy('is_default', 'desc')
                          ->orderBy('name')
                          ->get();

        return response()->json($locations);
    }

    /**
     * Store a newly created pickup location.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'required|string|max:100',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:1000',
                'is_default' => 'boolean',
                'active' => 'boolean'
            ]);

            $location = PickupLocation::create($validated);

            return response()->json([
                'message' => 'Pickup location created successfully',
                'data' => $location
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create pickup location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pickup location.
     */
    public function show(PickupLocation $pickupLocation): JsonResponse
    {
        return response()->json($pickupLocation);
    }

    /**
     * Update the specified pickup location.
     */
    public function update(Request $request, PickupLocation $pickupLocation): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'address_line1' => 'sometimes|required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'sometimes|required|string|max:100',
                'state' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'sometimes|required|string|max:100',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:1000',
                'is_default' => 'boolean',
                'active' => 'boolean'
            ]);

            $pickupLocation->update($validated);

            return response()->json([
                'message' => 'Pickup location updated successfully',
                'data' => $pickupLocation->fresh()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update pickup location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pickup location.
     */
    public function destroy(PickupLocation $pickupLocation): JsonResponse
    {
        try {
            // Check if this is the default location
            if ($pickupLocation->is_default) {
                return response()->json([
                    'message' => 'Cannot delete the default pickup location. Please set another location as default first.'
                ], 422);
            }

            $pickupLocation->delete();

            return response()->json([
                'message' => 'Pickup location deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete pickup location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set a pickup location as the default.
     */
    public function setDefault(PickupLocation $pickupLocation): JsonResponse
    {
        try {
            $pickupLocation->update(['is_default' => true]);

            return response()->json([
                'message' => 'Default pickup location updated successfully',
                'data' => $pickupLocation->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to set default pickup location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the active status of a pickup location.
     */
    public function toggleActive(PickupLocation $pickupLocation): JsonResponse
    {
        try {
            // Check if trying to deactivate the default location
            if ($pickupLocation->is_default && $pickupLocation->active) {
                return response()->json([
                    'message' => 'Cannot deactivate the default pickup location. Please set another location as default first.'
                ], 422);
            }

            $pickupLocation->update(['active' => !$pickupLocation->active]);

            return response()->json([
                'message' => 'Pickup location status updated successfully',
                'data' => $pickupLocation->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle pickup location status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active pickup locations for frontend dropdown/selection.
     */
    public function getActiveLocations(): JsonResponse
    {
        $locations = PickupLocation::active()
                                  ->orderBy('is_default', 'desc')
                                  ->orderBy('name')
                                  ->get(['id', 'name', 'city', 'is_default']);

        return response()->json($locations);
    }

    /**
     * Get the default pickup location.
     */
    public function getDefault(): JsonResponse
    {
        $defaultLocation = PickupLocation::default()->first();

        if (!$defaultLocation) {
            return response()->json([
                'message' => 'No default pickup location set'
            ], 404);
        }

        return response()->json($defaultLocation);
    }
}
