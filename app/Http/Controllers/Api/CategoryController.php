<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($data);

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        $category->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Categoría desactivada correctamente',
        ]);
    }
}
