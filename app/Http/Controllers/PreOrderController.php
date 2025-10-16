<?php

namespace App\Http\Controllers;

use App\Models\PreOrder;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PreOrderController extends Controller
{
    /**
     * Display a listing of the pre-orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PreOrder::with('category');

        // Add search functionality
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where('product_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('specifications', 'LIKE', "%{$searchTerm}%");
        }

        // Add category filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Add sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $preOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $preOrders,
            'message' => 'Pre-orders retrieved successfully'
        ]);
    }

    /**
     * Store a newly created pre-order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255', // Support both naming conventions
            'category_id' => 'required|exists:categories,id',
            'pre_order_price' => 'required|numeric|min:0',
            'preorder_price' => 'nullable|numeric|min:0', // Support both naming conventions
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'expected_availability' => 'required|string|max:255',
            'expected_availability_date' => 'nullable|string|max:255', // Support both naming conventions
            'power_output' => 'nullable|string|max:255',
            'warranty_period' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Normalize naming conventions
        if (isset($data['name']) && !isset($data['product_name'])) {
            $data['product_name'] = $data['name'];
        }
        if (isset($data['preorder_price']) && !isset($data['pre_order_price'])) {
            $data['pre_order_price'] = $data['preorder_price'];
        }
        if (isset($data['expected_availability_date']) && !isset($data['expected_availability'])) {
            $data['expected_availability'] = $data['expected_availability_date'];
        }

        // Remove alternative field names to avoid database errors
        unset($data['name'], $data['preorder_price'], $data['expected_availability_date']);

        // Set default deposit percentage if not provided
        if (!isset($data['deposit_percentage'])) {
            $data['deposit_percentage'] = 20; // Default 20%
        }

        // Normalize video URL if provided
        if (isset($data['video_url']) && !empty($data['video_url'])) {
            $normalizedVideoUrl = $this->validateYouTubeUrl($data['video_url']);
            if ($normalizedVideoUrl === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['video_url' => ['The video URL must be a valid YouTube link']]
                ], 422);
            }
            $data['video_url'] = $normalizedVideoUrl;
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                    $path = $image->storeAs('pre-orders', $filename, 'public');
                    $imagePaths[] = $path;
                }
            }
            $data['images'] = $imagePaths;
        }

        $preOrder = PreOrder::create($data);
        $preOrder->load('category');

        return response()->json([
            'success' => true,
            'data' => $this->formatPreOrder($preOrder),
            'message' => 'Pre-order created successfully'
        ], 201);
    }

    /**
     * Display the specified pre-order.
     */
    public function show(PreOrder $preOrder): JsonResponse
    {
        $preOrder->load('category');

        return response()->json([
            'success' => true,
            'data' => $preOrder,
            'message' => 'Pre-order retrieved successfully'
        ]);
    }

    /**
     * Update the specified pre-order.
     */
    public function update(Request $request, PreOrder $preOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255', // Support both naming conventions
            'category_id' => 'sometimes|required|exists:categories,id',
            'pre_order_price' => 'sometimes|required|numeric|min:0',
            'preorder_price' => 'nullable|numeric|min:0', // Support both naming conventions
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'expected_availability' => 'sometimes|required|string|max:255',
            'expected_availability_date' => 'nullable|string|max:255', // Support both naming conventions
            'power_output' => 'nullable|string|max:255',
            'warranty_period' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
            'video_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Normalize naming conventions
        if (isset($data['name']) && !isset($data['product_name'])) {
            $data['product_name'] = $data['name'];
        }
        if (isset($data['preorder_price']) && !isset($data['pre_order_price'])) {
            $data['pre_order_price'] = $data['preorder_price'];
        }
        if (isset($data['expected_availability_date']) && !isset($data['expected_availability'])) {
            $data['expected_availability'] = $data['expected_availability_date'];
        }

        // Remove alternative field names to avoid database errors
        unset($data['name'], $data['preorder_price'], $data['expected_availability_date']);

        // Normalize video URL if provided
        if (isset($data['video_url']) && !empty($data['video_url'])) {
            $normalizedVideoUrl = $this->validateYouTubeUrl($data['video_url']);
            if ($normalizedVideoUrl === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['video_url' => ['The video URL must be a valid YouTube link']]
                ], 422);
            }
            $data['video_url'] = $normalizedVideoUrl;
        }

        // Handle image removal
        if ($request->has('remove_images')) {
            $currentImages = $preOrder->images ?? [];
            $imagesToRemove = $request->get('remove_images');
            
            foreach ($imagesToRemove as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_diff($currentImages, [$imageToRemove]);
                }
            }
            
            $data['images'] = array_values($currentImages);
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $existingImages = $data['images'] ?? $preOrder->images ?? [];
            $newImagePaths = [];
            
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                    $path = $image->storeAs('pre-orders', $filename, 'public');
                    $newImagePaths[] = $path;
                }
            }
            
            $data['images'] = array_merge($existingImages, $newImagePaths);
        }

        $preOrder->update($data);
        $preOrder->load('category');

        return response()->json([
            'success' => true,
            'data' => $this->formatPreOrder($preOrder),
            'message' => 'Pre-order updated successfully'
        ]);
    }

    /**
     * Remove the specified pre-order.
     */
    public function destroy(PreOrder $preOrder): JsonResponse
    {
        // Delete associated images
        if ($preOrder->images) {
            foreach ($preOrder->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $preOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pre-order deleted successfully'
        ]);
    }

    /**
     * Get all categories for the dropdown
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Get pre-orders by category
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        $preOrders = PreOrder::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $preOrders,
            'message' => 'Pre-orders retrieved successfully'
        ]);
    }

    /**
     * Search pre-orders
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No search query provided'
            ]);
        }

        $preOrders = PreOrder::with('category')
            ->where('product_name', 'LIKE', "%{$query}%")
            ->orWhere('specifications', 'LIKE', "%{$query}%")
            ->orWhere('power_output', 'LIKE', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $preOrders,
            'message' => 'Search results retrieved successfully'
        ]);
    }

    /**
     * Validate and normalize YouTube URL
     */
    private function validateYouTubeUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // Extract video ID from various YouTube URL formats
        $videoId = null;
        
        // Standard watch URL: https://www.youtube.com/watch?v=VIDEO_ID
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // Short URL: https://youtu.be/VIDEO_ID
        elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // Embed URL: https://www.youtube.com/embed/VIDEO_ID
        elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }

        // Return normalized embed URL if valid video ID found
        if ($videoId) {
            return "https://www.youtube.com/embed/{$videoId}";
        }

        return null;
    }

    /**
     * Format pre-order data for API response
     */
    private function formatPreOrder($preOrder)
    {
        return [
            'id' => $preOrder->id,
            'name' => $preOrder->product_name,
            'product_name' => $preOrder->product_name,
            'pre_order_price' => $preOrder->pre_order_price,
            'preorder_price' => $preOrder->pre_order_price, // Support both naming conventions
            'deposit_percentage' => $preOrder->deposit_percentage,
            'deposit_amount' => $preOrder->deposit_amount, // From model accessor
            'expected_availability' => $preOrder->expected_availability,
            'expected_availability_date' => $preOrder->expected_availability, // Support both naming conventions
            'power_output' => $preOrder->power_output,
            'warranty_period' => $preOrder->warranty_period,
            'specifications' => $preOrder->specifications,
            'images' => $preOrder->images ? array_map(function($image) {
                return url('storage/' . $image);
            }, $preOrder->images) : [],
            'video_url' => $preOrder->video_url,
            'category' => $preOrder->category ? [
                'id' => $preOrder->category->id,
                'name' => $preOrder->category->name,
                'slug' => $preOrder->category->slug ?? null,
            ] : null,
            'created_at' => $preOrder->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $preOrder->updated_at->format('Y-m-d H:i:s'),
            'formatted_date' => $preOrder->created_at->format('M d, Y'),
        ];
    }
}
