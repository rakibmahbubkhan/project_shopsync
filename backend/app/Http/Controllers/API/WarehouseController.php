<?php
// app/Http/Controllers/API/WarehouseController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Warehouse::query();
            
            // Make sure we get all warehouses, not just active ones
            // Remove the is_active filter since it might not exist
            $query->orderBy('name');
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('address', 'LIKE', "%{$search}%");
                });
            }
            
            // Check if requesting all records (for dropdown)
            if ($request->boolean('all')) {
                $warehouses = $query->get();
                
                // Add stats to each warehouse
                foreach ($warehouses as $warehouse) {
                    $stats = DB::table('product_stocks')
                        ->where('warehouse_id', $warehouse->id)
                        ->select(
                            DB::raw('COALESCE(SUM(quantity), 0) as total_quantity'),
                            DB::raw('COUNT(*) as products_count'),
                            DB::raw('COALESCE(SUM(quantity * avg_cost), 0) as stock_value')
                        )
                        ->first();
                    
                    $warehouse->products_count = $stats->products_count ?? 0;
                    $warehouse->stock_value = $stats->stock_value ?? 0;
                    $warehouse->total_quantity = $stats->total_quantity ?? 0;
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $warehouses
                ]);
            }
            
            // For paginated requests
            $warehouses = $query->paginate($request->per_page ?? 15);
            
            // Add stats to each warehouse
            foreach ($warehouses as $warehouse) {
                $stats = DB::table('product_stocks')
                    ->where('warehouse_id', $warehouse->id)
                    ->select(
                        DB::raw('COALESCE(SUM(quantity), 0) as total_quantity'),
                        DB::raw('COUNT(*) as products_count'),
                        DB::raw('COALESCE(SUM(quantity * avg_cost), 0) as stock_value')
                    )
                    ->first();
                
                $warehouse->products_count = $stats->products_count ?? 0;
                $warehouse->stock_value = $stats->stock_value ?? 0;
                $warehouse->total_quantity = $stats->total_quantity ?? 0;
            }
            
            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            Log::error('Warehouse index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouses: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get all warehouses for dropdown (simplified version)
     */
    public function getDropdown(Request $request): JsonResponse
    {
        try {
            $warehouses = Warehouse::orderBy('name')
                ->get(['id', 'name', 'code']);
            
            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch warehouses dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouses',
                'data' => []
            ], 500);
        }
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:warehouses,code',
                'address' => 'nullable|string',
                'capacity' => 'nullable|numeric|min:0',
            ]);

            $warehouse = Warehouse::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'data' => $warehouse
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Warehouse store error: ' . $e->getMessage());
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
        try {
            $stats = DB::table('product_stocks')
                ->where('warehouse_id', $warehouse->id)
                ->select(
                    DB::raw('COALESCE(SUM(quantity), 0) as total_quantity'),
                    DB::raw('COUNT(*) as products_count'),
                    DB::raw('COALESCE(SUM(quantity * avg_cost), 0) as stock_value')
                )
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $warehouse->id,
                    'code' => $warehouse->code,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'capacity' => $warehouse->capacity,
                    'products_count' => $stats->products_count ?? 0,
                    'stock_value' => $stats->stock_value ?? 0,
                    'total_quantity' => $stats->total_quantity ?? 0,
                    'created_at' => $warehouse->created_at,
                    'updated_at' => $warehouse->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
                'address' => 'nullable|string',
                'capacity' => 'nullable|numeric|min:0',
                'is_active' => 'sometimes|boolean'
            ]);

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
            $hasStock = DB::table('product_stocks')
                ->where('warehouse_id', $warehouse->id)
                ->exists();
            
            if ($hasStock) {
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