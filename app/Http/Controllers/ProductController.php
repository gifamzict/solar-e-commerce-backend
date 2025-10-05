<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'power' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max per image
        ]);

        $data = $request->only([
            'name', 'category_id', 'price', 'stock', 'description', 'power', 'warranty', 'specifications'
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $data['images'] = $imagePaths;
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('category'),
        ], 201);
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
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'power' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $data = $request->only([
            'name', 'category_id', 'price', 'stock', 'description', 'power', 'warranty', 'specifications'
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $data['images'] = $imagePaths;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('category'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
