<?php

namespace App\Http\Controllers\API;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryLedger;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Controller;



class ProductController extends Controller
{

public function index(Request $request)
{
    $query = Product::query();

    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    if ($request->sort_by && $request->order) {
        $query->orderBy($request->sort_by, $request->order);
    }

    return response()->json(
        $query->paginate(10)
    );
}

/**
 * Generate a unique SKU for agricultural parts or machinery.
 * Format Example: PRD-2026-0001
 */
private function generateUniqueSKU()
{
    $prefix = 'PRD'; // Part/Product prefix
    $year = date('Y');
    
    // Find the last product created this year
    $lastProduct = Product::where('sku', 'LIKE', "{$prefix}-{$year}-%")
        ->orderBy('id', 'desc')
        ->first();

    if (!$lastProduct) {
        $number = 1;
    } else {
        // Extract the numeric part from the last SKU (e.g., from PRD-2026-0005, get 5)
        $lastNumber = (int) substr($lastProduct->sku, strrpos($lastProduct->sku, '-') + 1);
        $number = $lastNumber + 1;
    }

    // Pad with zeros to keep length consistent (e.g., 0001, 0010, 0100)
    $sequence = str_pad($number, 4, '0', STR_PAD_LEFT);

    return "{$prefix}-{$year}-{$sequence}";
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'brand_id' => 'nullable|exists:brands,id',
        'unit_id' => 'required|exists:units,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'cost_price' => 'required|numeric|min:0', // Your field name is cost_price
        'selling_price' => 'required|numeric|min:0',
        'alert_quantity' => 'required|integer|min:0',
        'initial_stock' => 'required|integer|min:0', // This will be stock_quantity
        'barcode' => 'nullable|string|unique:products,barcode',
        'sku' => 'nullable|string|unique:products,sku',
        'image' => 'nullable|image|max:2048', // If uploading image
        'status' => 'sometimes|boolean',
    ]);

    return DB::transaction(function () use ($validated, $request) {
        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Generate SKU if not provided
        $sku = $validated['sku'] ?? $this->generateUniqueSKU();
        
        // Create product with your actual field names
        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $sku,
            'barcode' => $validated['barcode'] ?? null,
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'stock_quantity' => $validated['initial_stock'], // initial_stock maps to stock_quantity
            'alert_quantity' => $validated['alert_quantity'],
            'image' => $imagePath,
            'status' => $validated['status'] ?? true,
        ]);

        // Log initial stock to InventoryLedger
        if ($validated['initial_stock'] > 0) {
            InventoryLedger::create([
                'product_id' => $product->id,
                'reference_type' => 'initial_stock',
                'reference_id' => $product->id, // Using product ID as reference
                'movement_type' => 'in',
                'quantity' => $validated['initial_stock'],
                'balance_after' => $validated['initial_stock'], // Initial balance
                'unit_cost' => $validated['cost_price'],
                'total_cost' => $validated['initial_stock'] * $validated['cost_price'],
                'user_id' => Auth::id(),
            ]);
        }

        // Load relationships for response
        $product->load(['category', 'brand', 'unit']);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    });
}

}