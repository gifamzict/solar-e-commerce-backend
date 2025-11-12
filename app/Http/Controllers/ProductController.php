<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products with enhanced filtering and search
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('power', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == 'true') {
            $query->where('stock', '>', 0);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort parameters
        $allowedSortFields = ['name', 'price', 'stock', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20); // Default 20 products per page
        $perPage = min($perPage, 100); // Maximum 100 products per page

        if ($request->has('paginated') && $request->paginated === 'false') {
            // Return all products without pagination
            $products = $query->get();
            
            $formattedProducts = $products->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'products' => $formattedProducts,
                'total' => $products->count(),
                'paginated' => false,
            ]);
        } else {
            // Return paginated results
            $products = $query->paginate($perPage);
            
            $formattedProducts = $products->getCollection()->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'products' => $formattedProducts,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'paginated' => true,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Product store request data:', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'power' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|url', // Now accepts URLs instead of files
            'video_url' => 'nullable|url',
        ]);

        $data = $request->only([
            'name', 'category_id', 'price', 'stock', 'description', 'power', 'warranty', 'specifications', 'images'
        ]);

        // Normalize video URL if provided
        if ($request->filled('video_url')) {
            $normalizedVideoUrl = $this->validateYouTubeUrl($request->video_url);
            if ($normalizedVideoUrl === null) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['video_url' => ['The video URL must be a valid YouTube link']]
                ], 422);
            }
            $data['video_url'] = $normalizedVideoUrl;
        }

        // Images are now URLs, no need to upload
        $data['images'] = $request->input('images', []);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $this->formatProduct($product->load('category')),
        ], 201);
    }

    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return 'Upload successful';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
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
     * Get stock status text
     */
    private function getStockStatus($stock)
    {
        if ($stock <= 0) {
            return 'Out of Stock';
        } elseif ($stock <= 5) {
            return 'Low Stock';
        } elseif ($stock <= 20) {
            return 'Limited Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'product' => $product->load('category'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        \Log::info('Product update request data:', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'power' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|url', // Now accepts URLs instead of files
            'video_url' => 'nullable|url',
        ]);

        $data = $request->only([
            'name', 'category_id', 'price', 'stock', 'description', 'power', 'warranty', 'specifications', 'images'
        ]);

        // Normalize video URL if provided
        if ($request->filled('video_url')) {
            $normalizedVideoUrl = $this->validateYouTubeUrl($request->video_url);
            if ($normalizedVideoUrl === null) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['video_url' => ['The video URL must be a valid YouTube link']]
                ], 422);
            }
            $data['video_url'] = $normalizedVideoUrl;
        }

        // Images are now URLs, no need to upload or delete old files
        if ($request->has('images')) {
            $data['images'] = $request->input('images', []);
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $this->formatProduct($product->load('category')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Images are stored in cloud storage, no need to delete from local storage
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get featured or recommended products
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', 8);
        
        // Get products with highest stock or most recent
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('stock', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $formattedProducts = $products->map(function ($product) {
            return $this->formatProduct($product);
        });

        return response()->json([
            'featured_products' => $formattedProducts,
        ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(Request $request, $categoryId)
    {
        $perPage = $request->get('per_page', 20);
        
        $products = Product::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $formattedProducts = $products->getCollection()->map(function ($product) {
            return $this->formatProduct($product);
        });

        return response()->json([
            'products' => $formattedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get products by category slug
     */
    public function byCategorySlug(Request $request, $slug)
    {
        // Find category by slug
        $category = \App\Models\Category::where('slug', $slug)->first();
        
        if (!$category) {
            return response()->json([
                'error' => 'Category not found',
                'slug' => $slug
            ], 404);
        }

        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Build query for products in this category
        $query = Product::with('category')
            ->where('category_id', $category->id);

        // Apply search if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('power', 'like', "%{$search}%");
            });
        }

        // Apply price filters
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == 'true') {
            $query->where('stock', '>', 0);
        }

        // Apply sorting
        $allowedSortFields = ['name', 'price', 'stock', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Check if pagination is requested
        if ($request->has('paginated') && $request->paginated === 'false') {
            // Return all products without pagination
            $products = $query->get();
            
            $formattedProducts = $products->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'products' => $formattedProducts,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ],
                'total' => $products->count(),
                'paginated' => false,
            ]);
        } else {
            // Return paginated results
            $products = $query->paginate($perPage);
            
            $formattedProducts = $products->getCollection()->map(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'products' => $formattedProducts,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ],
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'paginated' => true,
            ]);
        }
    }

    /**
     * Search products with autocomplete suggestions
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
                'products' => [],
            ]);
        }

        // Get product suggestions
        $products = Product::with('category')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('power', 'like', "%{$query}%");
            })
            ->where('stock', '>', 0)
            ->limit($limit)
            ->get();

        $formattedProducts = $products->map(function ($product) {
            return $this->formatProduct($product);
        });

        // Generate search suggestions
        $suggestions = $products->pluck('name')->unique()->take(5)->values();

        return response()->json([
            'suggestions' => $suggestions,
            'products' => $formattedProducts,
            'query' => $query,
        ]);
    }

    /**
     * Format product data for API response
     */
    private function formatProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'formatted_price' => '₦' . number_format($product->price, 2),
            'stock' => $product->stock,
            'in_stock' => $product->stock > 0,
            'stock_status' => $this->getStockStatus($product->stock),
            'power' => $product->power,
            'warranty' => $product->warranty,
            'specifications' => $product->specifications ?? [],
            'images' => $product->images ? array_map(function($image) {
                return url('storage/' . $image);
            }, $product->images) : [],
            'video_url' => $product->video_url,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug ?? null,
            ] : null,
            'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            'formatted_date' => $product->created_at->format('M d, Y'),
            'is_new' => $product->created_at->diffInDays(now()) <= 30, // Consider new if created within 30 days
        ];
    }
}
