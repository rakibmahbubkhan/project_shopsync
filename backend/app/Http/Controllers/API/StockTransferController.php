<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockTransferController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of stock transfers.
     */
    public function index(Request $request)
    {
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
        
        $transfers = $query->latest()->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Get products with stock information for transfer.
     */
    public function getAvailableProducts(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);
        
        $products = Product::with(['category', 'unit'])
            ->whereHas('stock', function($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id)
                  ->where('quantity', '>', 0);
            })
            ->get()
            ->map(function($product) use ($request) {
                $stock = $product->stock()->where('warehouse_id', $request->warehouse_id)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $stock ? $stock->quantity : 0,
                    'unit' => $product->unit?->name,
                    'category' => $product->category?->name,
                    'image' => $product->image
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created stock transfer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date'     => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'notes'             => 'nullable|string'
        ]);

        try {
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
            
            // Create transfer record
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id'   => $validated['to_warehouse_id'],
                'transfer_date'     => $validated['transfer_date'],
                'status'            => 'completed', // Auto-complete for now
                'notes'             => $validated['notes'] ?? null,
                'user_id'           => Auth::id(),
            ]);
            
            // Process each item
            foreach ($validated['items'] as $item) {
                // Get current average cost from source warehouse
                $cost = $this->stockService->getAverageCost(
                    $item['product_id'], 
                    $validated['from_warehouse_id']
                );
                
                // Create transfer item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'unit_cost'         => $cost,
                ]);
                
                // Decrease stock from Source Warehouse
                $this->stockService->decreaseStock(
                    $item['product_id'],
                    $validated['from_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'transfer_out',
                    $transfer->id,
                    Auth::id()
                );
                
                // Increase stock at Destination Warehouse
                $this->stockService->increaseStock(
                    $item['product_id'],
                    $validated['to_warehouse_id'],
                    $item['quantity'],
                    $cost,
                    'transfer_in',
                    $transfer->id,
                    Auth::id()
                );
            }
            
            // Update transfer totals
            $transfer->updateTotals();
            
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
    public function show(StockTransfer $stockTransfer)
    {
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
    }

    /**
     * Cancel a pending stock transfer.
     */
    public function cancel(StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This transfer cannot be cancelled'
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Reverse the stock movements
            foreach ($stockTransfer->items as $item) {
                // Return stock to source warehouse
                $this->stockService->increaseStock(
                    $item->product_id,
                    $stockTransfer->from_warehouse_id,
                    $item->quantity,
                    $item->unit_cost,
                    'transfer_cancelled',
                    $stockTransfer->id,
                    Auth::id()
                );
                
                // Remove stock from destination warehouse
                $this->stockService->decreaseStock(
                    $item->product_id,
                    $stockTransfer->to_warehouse_id,
                    $item->quantity,
                    $item->unit_cost,
                    'transfer_cancelled',
                    $stockTransfer->id,
                    Auth::id()
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
                'message' => 'Failed to cancel transfer'
            ], 500);
        }
    }

    /**
     * Delete a draft transfer.
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft transfers can be deleted'
            ], 422);
        }
        
        try {
            $stockTransfer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Transfer deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transfer'
            ], 500);
        }
    }

    /**
     * Generate transfer report.
     */
    public function report(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'warehouse_id' => 'nullable|exists:warehouses,id'
        ]);
        
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse'])
            ->whereBetween('transfer_date', [$request->from_date, $request->to_date])
            ->where('status', 'completed');
        
        if ($request->filled('warehouse_id')) {
            $query->where(function($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }
        
        $transfers = $query->get();
        
        $summary = [
            'total_transfers' => $transfers->count(),
            'total_items_transferred' => $transfers->sum('total_items'),
            'total_value' => $transfers->sum('total_cost'),
            'by_warehouse' => [
                'incoming' => $transfers->groupBy('to_warehouse_id')->map->sum('total_cost'),
                'outgoing' => $transfers->groupBy('from_warehouse_id')->map->sum('total_cost')
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'transfers' => $transfers,
                'summary' => $summary
            ]
        ]);
    }
}