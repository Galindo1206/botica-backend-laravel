<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:150',
            'generic_name' => 'nullable|string|max:150',
            'concentration' => 'nullable|string|max:80',
            'pharmaceutical_form' => 'nullable|string|max:80',
            'presentation' => 'nullable|string|max:120',
            'laboratory' => 'nullable|string|max:120',
            'barcode' => 'nullable|string|max:80|unique:products,barcode',
            'health_registration' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock' => 'nullable|integer|min:0',
            'sale_price' => 'required|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = $path;
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'data' => $product->load('category'),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json(
            $product->load(['category', 'batches'])
        );
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:150',
            'generic_name' => 'nullable|string|max:150',
            'concentration' => 'nullable|string|max:80',
            'pharmaceutical_form' => 'nullable|string|max:80',
            'presentation' => 'nullable|string|max:120',
            'laboratory' => 'nullable|string|max:120',
            'barcode' => 'nullable|string|max:80|unique:products,barcode,' . $product->id,
            'health_registration' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock' => 'nullable|integer|min:0',
            'sale_price' => 'required|numeric|min:0',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ]);
        if ($request->hasFile('image')) {

            if (
                $product->image_path &&
                Storage::disk('public')->exists($product->image_path)
            ) {

                Storage::disk('public')->delete($product->image_path);
            }

            $path = $request->file('image')->store('products', 'public');

            $data['image_path'] = $path;
        }
        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'data' => $product->load('category'),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Producto desactivado correctamente',
        ]);
    }
}
