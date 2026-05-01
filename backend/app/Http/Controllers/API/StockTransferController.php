<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockTransferController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
        // Apply middleware in constructor instead
    }

    /**
     * Get all warehouses for dropdown
     */
    public function getWarehouses(): JsonResponse
    {
        try {
            $warehouses = Warehouse::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'address', 'is_active']);
            
            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch warehouses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouses: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Display a listing of stock transfers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.product']);
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('from_date')) {
                $query->whereDate('transfer_date', '>=', $request->from_date);
            }
            
            if ($request->filled('to_date')) {
                $query->whereDate('transfer_date', '<=', $request->to_date);
            }
            
            if ($request->filled('warehouse_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('from_warehouse_id', $request->warehouse_id)
                      ->orWhere('to_warehouse_id', $request->warehouse_id);
                });
            }
            
            $transfers = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch transfers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load transfers: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get products with stock information for transfer.
     */
    public function getAvailableProducts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id'
            ]);
            
            $warehouseId = $request->warehouse_id;
            
            // Try different relationship names based on your database structure
            $products = Product::with(['category', 'unit'])
                ->get()
                ->filter(function($product) use ($warehouseId) {
                    // Check if product has stock in the selected warehouse
                    // Try different possible relationship names
                    $stock = null;
                    
                    if (method_exists($product, 'stocks')) {
                        $stock = $product->stocks()->where('warehouse_id', $warehouseId)->first();
                    } elseif (method_exists($product, 'stock')) {
                        $stock = $product->stock()->where('warehouse_id', $warehouseId)->first();
                    } elseif (method_exists($product, 'productStock')) {
                        $stock = $product->productStock()->where('warehouse_id', $warehouseId)->first();
                    } else {
                        // Direct query as fallback
                        $stock = \DB::table('product_stocks')
                            ->where('product_id', $product->id)
                            ->where('warehouse_id', $warehouseId)
                            ->first();
                    }
                    
                    return $stock && $stock->quantity > 0;
                })
                ->map(function($product) use ($warehouseId) {
                    // Get stock quantity
                    $stock = null;
                    $quantity = 0;
                    
                    if (method_exists($product, 'stocks')) {
                        $stock = $product->stocks()->where('warehouse_id', $warehouseId)->first();
                        $quantity = $stock ? (float) $stock->quantity : 0;
                    } elseif (method_exists($product, 'stock')) {
                        $stock = $product->stock()->where('warehouse_id', $warehouseId)->first();
                        $quantity = $stock ? (float) $stock->quantity : 0;
                    } elseif (method_exists($product, 'productStock')) {
                        $stock = $product->productStock()->where('warehouse_id', $warehouseId)->first();
                        $quantity = $stock ? (float) $stock->quantity : 0;
                    } else {
                        $stock = \DB::table('product_warehouse')
                            ->where('product_id', $product->id)
                            ->where('warehouse_id', $warehouseId)
                            ->first();
                        $quantity = $stock ? (float) $stock->quantity : 0;
                    }
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? null,
                        'barcode' => $product->barcode ?? null,
                        'current_stock' => $quantity,
                        'unit' => $product->unit ? $product->unit->name : 'Unit',
                        'category' => $product->category ? $product->category->name : 'Uncategorized',
                        'image' => $product->image ?? null,
                        'price' => (float) ($product->selling_price ?? 0),
                        'cost' => (float) ($product->cost ?? 0)
                    ];
                })
                ->values();
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products loaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch available products: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Store a newly created stock transfer.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
                'transfer_date'     => 'required|date',
                'items'             => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity'   => 'required|numeric|min:0.01',
                'notes'             => 'nullable|string'
            ]);

            DB::beginTransaction();
            
            // Validate stock availability for each item
            foreach ($validated['items'] as $item) {
                $currentStock = $this->stockService->getCurrentStock(
                    $item['product_id'],
                    $validated['from_warehouse_id']
                );
                
                if ($currentStock < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$currentStock}, Requested: {$item['quantity']}");
                }
            }
            
            // Generate reference number
            $lastTransfer = StockTransfer::orderBy('id', 'desc')->first();
            $nextId = ($lastTransfer ? $lastTransfer->id : 0) + 1;
            $referenceNo = 'TRF-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            // Create transfer record
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id'   => $validated['to_warehouse_id'],
                'transfer_date'     => $validated['transfer_date'],
                'reference_no'      => $referenceNo,
                'status'            => 'completed',
                'notes'             => $validated['notes'] ?? null,
                'user_id'           => Auth::id(),
            ]);
            
            $totalCost = 0;
            
            // Process each item using StockService
            foreach ($validated['items'] as $item) {
                // Get current average cost from source warehouse
                $cost = $this->stockService->getAverageCost(
                    $item['product_id'], 
                    $validated['from_warehouse_id']
                );
                
                $itemTotalCost = $item['quantity'] * $cost;
                $totalCost += $itemTotalCost;
                
                // Create transfer item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'unit_cost'         => $cost,
                    'total_cost'        => $itemTotalCost,
                ]);
                
                // Use StockService for stock movements
                $this->stockService->decreaseStock(
                    $item['product_id'],
                    $validated['from_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'stock_transfer',
                    $transfer->id,
                    Auth::id(),
                    "Transfer to warehouse #{$validated['to_warehouse_id']}"
                );
                
                $this->stockService->increaseStock(
                    $item['product_id'],
                    $validated['to_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'stock_transfer',
                    $transfer->id,
                    Auth::id(),
                    "Transfer from warehouse #{$validated['from_warehouse_id']}"
                );
            }
            
            // Update transfer totals
            $transfer->update([
                'total_items' => count($validated['items']),
                'total_cost' => $totalCost
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully',
                'data' => $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock transfer failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        try {
            $stockTransfer->load([
                'fromWarehouse', 
                'toWarehouse', 
                'user',
                'items.product.category',
                'items.product.unit'
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $stockTransfer
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch transfer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load transfer details'
            ], 500);
        }
    }

    /**
     * Cancel a pending stock transfer.
     */
    public function cancel(StockTransfer $stockTransfer): JsonResponse
    {
        try {
            if (!$stockTransfer->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This transfer cannot be cancelled'
                ], 422);
            }
            
            DB::beginTransaction();
            
            // Reverse the stock movements
            foreach ($stockTransfer->items as $item) {
                // Return stock to source warehouse
                $this->increaseStockInDB(
                    $item->product_id,
                    $stockTransfer->from_warehouse_id,
                    $item->quantity,
                    $item->unit_cost
                );
                
                // Remove stock from destination warehouse
                $this->decreaseStockInDB(
                    $item->product_id,
                    $stockTransfer->to_warehouse_id,
                    $item->quantity
                );
            }
            
            $stockTransfer->status = 'cancelled';
            $stockTransfer->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transfer cancelled successfully',
                'data' => $stockTransfer
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer cancellation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for direct database operations
    private function getCurrentStockFromDB($productId, $warehouseId)
    {
        $stock = \DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        return $stock ? (float) $stock->quantity : 0;
    }

    private function getAverageCostFromDB($productId, $warehouseId)
    {
        $stock = \DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        return $stock ? (float) ($stock->avg_cost ?? 0) : 0;
    }

    private function decreaseStockInDB($productId, $warehouseId, $quantity)
    {
        return \DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('quantity', $quantity);
    }

    private function increaseStockInDB($productId, $warehouseId, $quantity, $cost = 0)
    {
        $existing = \DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        if ($existing) {
            return \DB::table('product_stocks')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->increment('quantity', $quantity);
        } else {
            return \DB::table('product_stocks')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'avg_cost' => $cost,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}