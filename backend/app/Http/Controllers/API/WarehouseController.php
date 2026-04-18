<?php
// app/Http/Controllers/API/WarehouseController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::query();
        
        // Add eager loading for counts
        $query->withCount('products');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }
        
        $warehouses = $query->latest()->paginate($request->per_page ?? 15);
        
        // Transform the data to include products_count
        $warehouses->getCollection()->transform(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'capacity' => $warehouse->capacity,
                'products_count' => $warehouse->products_count ?? 0,
                'stock_value' => $warehouse->stocks()->sum('quantity') ?? 0,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string',
            'capacity' => 'nullable|numeric|min:0',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
        ]);

        try {
            $warehouse = Warehouse::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'data' => $warehouse
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse->loadCount('products');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'capacity' => $warehouse->capacity,
                'products_count' => $warehouse->products_count,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
            ]
        ]);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'capacity' => 'nullable|numeric|min:0',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            $warehouse->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
                'data' => $warehouse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            // Check if warehouse has any stock
            if ($warehouse->stocks()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete warehouse that contains stock.'
                ], 400);
            }
            
            $warehouse->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete warehouse: ' . $e->getMessage()
            ], 500);
        }
    }
}