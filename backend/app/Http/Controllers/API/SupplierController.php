<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Supplier::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string'
        ]);

        $supplier = Supplier::create($validated);
        return response()->json($supplier, 201);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string'
        ]);

        $supplier->update($validated);
        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->purchases()->exists()) {
            return response()->json(['message' => 'Cannot delete supplier with purchase history.'], 422);
        }
        $supplier->delete();
        return response()->json(null, 204);
    }
}