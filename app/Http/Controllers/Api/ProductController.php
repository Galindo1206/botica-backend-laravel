<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->withImageUrl($product));

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
            'data' => $this->withImageUrl($product->load('category')),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json(
            $this->withImageUrl($product->load(['category', 'batches']))
        );
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'products' => 'required|array|min:1|max:500',
            'products.*' => 'required|array',
        ]);

        $created = 0;
        $updated = 0;
        $errors = [];
        $products = [];

        foreach ($data['products'] as $index => $row) {
            $rowNumber = $index + 1;
            $payload = $this->normalizeImportRow($row);
            $existingProduct = $payload['barcode']
                ? Product::where('barcode', $payload['barcode'])->first()
                : null;

            $validator = Validator::make($payload, [
                'category_id' => 'nullable|integer|exists:categories,id',
                'name' => 'required|string|max:150',
                'generic_name' => 'nullable|string|max:150',
                'concentration' => 'nullable|string|max:80',
                'pharmaceutical_form' => 'nullable|string|max:80',
                'presentation' => 'nullable|string|max:120',
                'laboratory' => 'nullable|string|max:120',
                'barcode' => 'nullable|string|max:80|unique:products,barcode,' . ($existingProduct?->id ?? 'NULL'),
                'health_registration' => 'nullable|string|max:80',
                'description' => 'nullable|string',
                'stock' => 'nullable|integer|min:0',
                'sale_price' => 'required|numeric|min:0',
                'requires_prescription' => 'boolean',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ];
                continue;
            }

            $validated = $validator->validated();

            if ($existingProduct) {
                $existingProduct->update($validated);
                $updated++;
                $products[] = $this->withImageUrl($existingProduct->load('category'));
                continue;
            }

            $product = Product::create($validated);
            $created++;
            $products[] = $this->withImageUrl($product->load('category'));
        }

        return response()->json([
            'message' => 'Importacion de productos finalizada',
            'summary' => [
                'received' => count($data['products']),
                'created' => $created,
                'updated' => $updated,
                'failed' => count($errors),
            ],
            'errors' => $errors,
            'data' => $products,
        ], count($errors) > 0 ? 207 : 201);
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
            'data' => $this->withImageUrl($product->load('category')),
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

    private function withImageUrl(Product $product): array
    {
        $imageExists = $product->image_path &&
            Storage::disk('public')->exists($product->image_path);

        return [
            ...$product->toArray(),
            'image_url' => $imageExists
                ? asset('storage/' . $product->image_path)
                : null,
        ];
    }

    private function normalizeImportRow(array $row): array
    {
        $nullableText = fn (string $key) => isset($row[$key]) && trim((string) $row[$key]) !== ''
            ? trim((string) $row[$key])
            : null;

        return [
            'category_id' => $row['category_id'] ?? null,
            'name' => trim((string) ($row['name'] ?? '')),
            'generic_name' => $nullableText('generic_name'),
            'concentration' => $nullableText('concentration'),
            'pharmaceutical_form' => $nullableText('pharmaceutical_form'),
            'presentation' => $nullableText('presentation'),
            'laboratory' => $nullableText('laboratory'),
            'barcode' => $nullableText('barcode'),
            'health_registration' => $nullableText('health_registration'),
            'description' => $nullableText('description'),
            'stock' => max(0, (int) ($row['stock'] ?? 0)),
            'sale_price' => $row['sale_price'] ?? 0,
            'requires_prescription' => filter_var($row['requires_prescription'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
