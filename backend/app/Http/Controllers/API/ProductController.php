<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\InventoryLedger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * List products with search, sorting, and pagination.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $sortField = $request->sort_by ?? 'created_at';
        $order = $request->order ?? 'desc';
        
        return response()->json($query->orderBy($sortField, $order)->paginate(10));
    }

    /**
     * Store a new agricultural part or machine.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'initial_stock' => 'sometimes|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => 'nullable|image|max:2048',
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
            
            // Set initial stock (default to 0 if not provided)
            $initialStock = $validated['initial_stock'] ?? 0;
            
            // Create product with your actual field names
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $sku,
                'barcode' => $validated['barcode'] ?? null,
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'cost_price' => $validated['cost_price'],
                'selling_price' => $validated['selling_price'],
                'stock_quantity' => $initialStock,
                'alert_quantity' => $validated['alert_quantity'],
                'image' => $imagePath,
                'status' => $validated['status'] ?? true,
            ]);

            // Log initial stock to InventoryLedger if initial stock > 0
            if ($initialStock > 0) {
                InventoryLedger::create([
                    'product_id' => $product->id,
                    'reference_type' => 'initial_stock',
                    'reference_id' => $product->id,
                    'movement_type' => 'in',
                    'quantity' => $initialStock,
                    'balance_after' => $initialStock,
                    'unit_cost' => $validated['cost_price'],
                    'total_cost' => $initialStock * $validated['cost_price'],
                    'user_id' => Auth::id(),
                ]);
            }

            // Load relationships for response
            $product->load(['category', 'brand', 'unit', 'warehouse']);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        });
    }

    /**
     * Update product details.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'barcode' => ['nullable', 'string', Rule::unique('products')->ignore($product->id)],
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean'
        ]);

        return DB::transaction(function () use ($validated, $request, $product) {
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // Remove warehouse_id from update if you don't want to allow warehouse changes
            // or add logic to handle warehouse changes with inventory transfer
            
            $product->update($validated);

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->load(['category', 'brand', 'unit', 'warehouse'])
            ]);
        });
    }

    /**
     * Delete a product if it has no transaction history.
     */
    public function destroy(Product $product)
    {
        // Check if product has any inventory ledger entries
        if ($product->inventoryLedgers()->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with inventory history.'
            ], 422);
        }

        // Check if linked to sales or purchases
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with transaction history.'
            ], 422);
        }

        $product->delete();
        
        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }

    /**
     * Helper to generate unique SKU for workshop parts.
     * Format: PRD-2026-0001
     */
    private function generateUniqueSKU()
    {
        $prefix = 'PRD';
        $year = date('Y');
        
        $lastProduct = Product::where('sku', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastProduct) {
            $number = 1;
        } else {
            // Extract the numeric part from the last SKU
            $lastNumber = (int) substr($lastProduct->sku, strrpos($lastProduct->sku, '-') + 1);
            $number = $lastNumber + 1;
        }

        return "{$prefix}-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get low stock products based on alert quantity
     */
    public function lowStock()
    {
        $products = Product::with(['category', 'unit'])
            ->whereRaw('stock_quantity <= alert_quantity')
            ->where('status', true)
            ->get();

        return response()->json($products);
    }

    /**
     * Get product inventory history
     */
    public function inventoryHistory(Product $product)
    {
        $ledgers = $product->inventoryLedgers()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'product' => $product->load(['category', 'unit']),
            'history' => $ledgers,
            'current_stock' => $product->stock_quantity
        ]);
    }
}