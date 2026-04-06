<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\InventoryLedger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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

    public function create()
    {
        return response()->json([
            'categories' => \App\Models\Category::all(),
            'brands'     => \App\Models\Brand::all(),
            'units'      => \App\Models\Unit::all(),
            'warehouses' => \App\Models\Warehouse::all(),
        ]);
    }

    /**
     * Store a new agricultural part or machine.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'category_id'     => 'required|exists:categories,id',
        'brand_id'        => 'nullable|exists:brands,id',
        'unit_id'         => 'required|exists:units,id',
        'cost_price'      => 'required|numeric|min:0',
        'selling_price'   => 'required|numeric|min:0',
        'alert_quantity'  => 'required|integer|min:0',
        'warehouse_id'    => 'required|exists:warehouses,id',
        'stock_quantity'   => 'nullable|numeric|min:0',
        'barcode'         => 'nullable|string|unique:products,barcode',
        'image'           => 'nullable|image|max:2048',
        'status'          => 'sometimes|boolean',
    ]);

    return DB::transaction(function () use ($validated, $request) {
        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Auto-generate SKU if not provided
        $sku = $validated['sku'] ?? ('SKU-' . strtoupper(uniqid()));
        
        // Set initial stock (default to 0 if not provided)
        $initialStock = $validated['stock_quantity'] ?? 0;
        
        // Create product with all fields
        $product = Product::create([
            'name'           => $validated['name'],
            'sku'            => $sku,
            'barcode'        => $validated['barcode'] ?? null,
            'category_id'    => $validated['category_id'],
            'brand_id'       => $validated['brand_id'] ?? null,
            'unit_id'        => $validated['unit_id'],
            'cost_price'     => $validated['cost_price'],
            'selling_price'  => $validated['selling_price'],
            'alert_quantity' => $validated['alert_quantity'],
            'image'          => $imagePath,
            'status'         => $validated['status'] ?? true,
        ]);

        // CRITICAL: Create the stock entry for the specific warehouse
        \App\Models\ProductStock::create([
            'product_id'   => $product->id,
            'warehouse_id' => $validated['warehouse_id'],
            'quantity'     => $initialStock,
            'avg_cost'     => $validated['cost_price'],
        ]);

        // If using StockService, update the product's stock_quantity field
        if ($initialStock > 0) {
            $product->update(['stock_quantity' => $initialStock]);
            
            // Optional: Create stock movement record if you have stock history table
            // StockMovement::create([
            //     'product_id'     => $product->id,
            //     'warehouse_id'   => $validated['warehouse_id'],
            //     'type'           => 'initial_stock',
            //     'quantity'       => $initialStock,
            //     'reference_type' => 'product_creation',
            //     'reference_id'   => $product->id,
            //     'created_by'     => Auth::id(),
            // ]);
        }

        // Load relationships for response
        $product->load(['category', 'brand', 'unit']);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
            'initial_stock_entry' => [
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $initialStock,
                'avg_cost' => $validated['cost_price']
            ]
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
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'alert_quantity' => 'required|integer|min:0',
        'stock_quantity' => 'nullable|integer|min:0',
        'status' => 'required|in:0,1,true,false',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // ✅ Already nullable
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $validated['image'] = $request->file('image')->store('products', 'public');
    } else {
        // ✅ Remove image from validated data if not present
        unset($validated['image']);
    }

    // ✅ Remove stock_quantity from validated data as it shouldn't be updated here
    unset($validated['stock_quantity']);

    $product->update($validated);

    return response()->json($product->load(['category', 'brand', 'unit', 'warehouses']));
}

    /**
     * Delete a product if it has no transaction history.
     */
    public function destroy(Product $product)
    {
        try {
            return DB::transaction(function () use ($product) {
                // 1. Check if the product is in any sales/purchases
                if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
                    return response()->json([
                        'message' => 'Cannot delete product: It has transaction history (Sales/Purchases).'
                    ], 422);
                }

                // 2. Safe to delete related stock records first
                $product->stocks()->delete();
                
                // 3. Delete related ledger entries (Only if you want to wipe dev data)
                $product->inventoryLedgers()->delete();

                // 4. Finally delete the product
                $product->delete();

                return response()->json(['message' => 'Product and related stock data deleted successfully.']);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
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