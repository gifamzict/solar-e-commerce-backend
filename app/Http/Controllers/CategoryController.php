<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function show(Category $category)
    {
        return response()->json([
            'category' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only('name', 'slug', 'description'));

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->only('name', 'slug', 'description'));

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
