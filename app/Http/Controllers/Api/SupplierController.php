<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'ruc' => 'nullable|string|max:20|unique:suppliers,ruc',
            'contact_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($data);

        return response()->json([
            'message' => 'Proveedor creado correctamente',
            'data' => $supplier,
        ], 201);
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'ruc' => 'nullable|string|max:20|unique:suppliers,ruc,' . $supplier->id,
            'contact_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $supplier->update($data);

        return response()->json([
            'message' => 'Proveedor actualizado correctamente',
            'data' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Proveedor desactivado correctamente',
        ]);
    }
}
