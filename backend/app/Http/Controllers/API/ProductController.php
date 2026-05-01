<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockService;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;


class ProductController extends Controller
{
    /**
     * List products with search, sorting, and pagination.
     */
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }


    public function index(Request $request)
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        
        $products = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($request) {
            // Build the query with stock calculation
            $query = Product::with(['category:id,name', 'brand:id,name', 'unit:id,name'])
                ->leftJoin('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->select(
                    'products.*',
                    DB::raw('COALESCE(SUM(product_stocks.quantity), 0) as real_stock_quantity')
                )
                ->groupBy('products.id');
            
            // Apply search filter
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('products.name', 'like', "%{$request->search}%")
                      ->orWhere('products.sku', 'like', "%{$request->search}%");
                });
            }
            
            // Apply category filter
            if ($request->category_id) {
                $query->where('products.category_id', $request->category_id);
            }
            
            // Apply warehouse filter (if specific warehouse is selected)
            if ($request->warehouse_id) {
                $query->where('product_stocks.warehouse_id', $request->warehouse_id);
            }
            
            // Sorting - allow sorting by real stock quantity
            $sortField = $request->sort_by;
            $order = $request->order === 'asc' ? 'asc' : 'desc';
            
            switch ($sortField) {
                case 'name':
                    $query->orderBy('products.name', $order);
                    break;
                case 'sku':
                    $query->orderBy('products.sku', $order);
                    break;
                case 'price':
                case 'selling_price':
                    $query->orderBy('products.selling_price', $order);
                    break;
                case 'stock_quantity':
                case 'real_stock':
                    $query->orderBy('real_stock_quantity', $order);
                    break;
                default:
                    $query->orderBy('products.created_at', $order);
                    break;
            }
            
            $perPage = $request->per_page ?? 10;
            $results = $query->paginate($perPage);
            
            // Transform the results to include real_stock as stock_quantity for frontend compatibility
            $results->getCollection()->transform(function ($product) {
                $product->stock_quantity = $product->real_stock_quantity;
                return $product;
            });
            
            return $results;
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
            
            if ($initialStock > 0) {
                // Use StockService to add initial stock
                $this->stockService->increaseStock(
                    $product->id,
                    $validated['warehouse_id'],
                    $initialStock,
                    $validated['cost_price'],
                    'product_creation',
                    $product->id,
                    Auth::id(),
                    "Initial stock on product creation"
                );
            } else {
                // Create stock record with zero quantity
                ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'quantity' => 0,
                    'avg_cost' => $validated['cost_price'],
                    'last_updated_by' => Auth::id(),
                ]);
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
        
        // Handle image upload
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

                // Delete image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
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
    private function clearProductCache()
    {
        // For file/database cache drivers that don't support wildcards,
        // we need to clear specific known keys
        Cache::forget('product_form_data');
        Cache::forget('low_stock_products');
        
        // Clear product list caches (they have dynamic keys)
        // Since we can't use wildcards with file cache, we'll use a prefix approach
        $this->clearProductListCaches();
    }
    
    /**
     * Clear product list caches by scanning cache store
     */
    private function clearProductListCaches()
    {
        // For file cache, we need to manually clear cache files with products_ prefix
        $cachePath = storage_path('framework/cache/data');
        
        if (file_exists($cachePath)) {
            $files = glob($cachePath . '/products_*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        
        // For database cache, we would need to delete records with key like 'products_%'
        // But for simplicity, we can just forget a known set or use a version counter
        Cache::forget('products_version');
        
        // Increment version to invalidate cached product lists
        $version = Cache::get('products_version', 0) + 1;
        Cache::forever('products_version', $version);
    }
    
    /**
     * Get a product by ID with caching
     */
    public function show($id)
    {
        $cacheKey = 'product_' . $id;
        
        $product = Cache::remember($cacheKey, now()->addHours(1), function() use ($id) {
            return Product::with(['category', 'brand', 'unit', 'stocks.warehouse'])
                ->findOrFail($id);
        });
        
        return response()->json($product);
    }
    
    /**
     * Bulk update stock quantities
     */
    public function bulkUpdateStock(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.warehouse_id' => 'required|exists:warehouses,id',
            'updates.*.quantity' => 'required|numeric|min:0',
        ]);
        
        DB::transaction(function () use ($validated) {
            foreach ($validated['updates'] as $update) {
                \App\Models\ProductStock::updateOrCreate(
                    [
                        'product_id' => $update['product_id'],
                        'warehouse_id' => $update['warehouse_id'],
                    ],
                    ['quantity' => $update['quantity']]
                );
            }
        });
        
        $this->clearProductCache();
        
        return response()->json(['message' => 'Stock updated successfully']);
    }
}