<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    public function index()
    {
        $now = now();

        $publications = Publication::with(['product.category', 'category'])
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn (Publication $publication) => $this->withImageUrl($publication));

        return response()->json($publications);
    }

    public function adminIndex()
    {
        $publications = Publication::with(['product.category', 'category'])
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn (Publication $publication) => $this->withImageUrl($publication));

        return response()->json($publications);
    }

    public function show(Publication $publication)
    {
        if (!$publication->is_active) {
            abort(404);
        }

        return response()->json(
            $this->withImageUrl($publication->load(['product.category', 'category']))
        );
    }

    public function adminShow(Publication $publication)
    {
        return response()->json(
            $this->withImageUrl($publication->load(['product.category', 'category']))
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('publications', 'public');
        }

        $publication = Publication::create($data);

        return response()->json([
            'message' => 'Publicacion creada correctamente',
            'data' => $this->withImageUrl($publication->load(['product.category', 'category'])),
        ], 201);
    }

    public function update(Request $request, Publication $publication)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            if ($publication->image_path && Storage::disk('public')->exists($publication->image_path)) {
                Storage::disk('public')->delete($publication->image_path);
            }

            $data['image_path'] = $request->file('image')->store('publications', 'public');
        }

        $publication->update($data);

        return response()->json([
            'message' => 'Publicacion actualizada correctamente',
            'data' => $this->withImageUrl($publication->load(['product.category', 'category'])),
        ]);
    }

    public function destroy(Publication $publication)
    {
        $publication->update(['is_active' => false]);

        return response()->json([
            'message' => 'Publicacion desactivada correctamente',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:160',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'type' => 'required|string|max:40',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
    }

    private function withImageUrl(Publication $publication): array
    {
        $imageExists = $publication->image_path &&
            Storage::disk('public')->exists($publication->image_path);

        return [
            ...$publication->toArray(),
            'image_url' => $imageExists
                ? asset('storage/' . $publication->image_path)
                : null,
        ];
    }
}
