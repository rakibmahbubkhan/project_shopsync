<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * List products with search, sorting, and pagination.
     */
    public function index(Request $request)
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        
        $products = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($request) {
            $query = Product::with(['category:id,name', 'brand:id,name', 'unit:id,name']);
            
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('sku', 'like', "%{$request->search}%");
                });
            }
            
            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }
            
            $sortField = in_array($request->sort_by, ['name', 'sku', 'price', 'stock_quantity']) ? $request->sort_by : 'created_at';
            $order = $request->order === 'asc' ? 'asc' : 'desc';
            
            return $query->orderBy($sortField, $order)->paginate($request->per_page ?? 10);
        });
        
        return response()->json($products);
    }

    /**
     * Get form data (categories, brands, units, warehouses)
     */
    public function getFormData()
    {
        $cacheKey = 'product_form_data';
        
        $data = Cache::remember($cacheKey, now()->addHours(6), function() {
            return [
                'categories' => \App\Models\Category::select('id', 'name')->get(),
                'brands' => \App\Models\Brand::select('id', 'name')->get(),
                'units' => \App\Models\Unit::select('id', 'name')->get(),
                'warehouses' => \App\Models\Warehouse::select('id', 'name')->get(),
            ];
        });
        
        return response()->json($data);
    }

    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'alert_quantity' => 'required|integer|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
            'stock_quantity' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'image' => 'nullable|image|max:2048',
            'status' => 'sometimes|boolean',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $imagePath = $request->hasFile('image') 
                ? $request->file('image')->store('products', 'public') 
                : null;
            
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? ('SKU-' . strtoupper(uniqid())),
                'barcode' => $validated['barcode'] ?? null,
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'cost_price' => $validated['cost_price'],
                'selling_price' => $validated['selling_price'],
                'alert_quantity' => $validated['alert_quantity'],
                'image' => $imagePath,
                'status' => $validated['status'] ?? true,
            ]);

            $initialStock = $validated['stock_quantity'] ?? 0;
            
            \App\Models\ProductStock::create([
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $initialStock,
                'avg_cost' => $validated['cost_price'],
            ]);

            if ($initialStock > 0) {
                $product->update(['stock_quantity' => $initialStock]);
            }

            $this->clearProductCache();
            
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load(['category', 'brand', 'unit'])
            ], 201);
        });
    }

    /**
     * Update product details
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
            'status' => 'required|in:0,1,true,false',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product->update($validated);
        
        $this->clearProductCache();
        
        return response()->json($product->load(['category', 'brand', 'unit']));
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product)
    {
        try {
            return DB::transaction(function () use ($product) {
                if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
                    return response()->json([
                        'message' => 'Cannot delete product: It has transaction history.'
                    ], 422);
                }

                $product->stocks()->delete();
                $product->inventoryLedgers()->delete();
                $product->delete();
                
                $this->clearProductCache();
                
                return response()->json(['message' => 'Product deleted successfully.']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock()
    {
        $cacheKey = 'low_stock_products';
        
        $products = Cache::remember($cacheKey, now()->addMinutes(15), function() {
            return Product::with(['category:id,name', 'unit:id,name'])
                ->whereRaw('stock_quantity <= alert_quantity')
                ->where('status', true)
                ->select('id', 'name', 'sku', 'stock_quantity', 'alert_quantity', 'category_id', 'unit_id')
                ->get();
        });
        
        return response()->json($products);
    }

    /**
     * Clear all product-related caches
     */
    /**
 * Clear all product-related caches
 */
    private function clearProductCache()
    {
        // Clear only product-related cache keys instead of full flush
        Cache::forget('products_*');
        Cache::forget('low_stock_products');
        Cache::forget('product_form_data');
        
        // If using cache tags (Redis/Memcached)
        if (method_exists(Cache::store(), 'tags')) {
            Cache::tags(['products'])->flush();
        }
    }

    
}