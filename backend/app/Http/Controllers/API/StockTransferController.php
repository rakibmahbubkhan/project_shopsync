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
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all warehouses for dropdown
     */
    public function getWarehouses(): JsonResponse
    {
        try {
            $warehouses = Warehouse::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'address']);
            
            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch warehouses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouses'
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
            
            $transfers = $query->latest()->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch transfers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load transfers'
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
            
            $products = Product::with(['category', 'unit'])
                ->whereHas('stocks', function($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id)
                      ->where('quantity', '>', 0);
                })
                ->get()
                ->map(function($product) use ($request) {
                    $stock = $product->stocks()->where('warehouse_id', $request->warehouse_id)->first();
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'current_stock' => $stock ? (float) $stock->quantity : 0,
                        'unit' => $product->unit?->name,
                        'category' => $product->category?->name,
                        'image' => $product->image
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch available products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products'
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
            $referenceNo = 'TRF-' . date('Ymd') . '-' . str_pad(StockTransfer::max('id') + 1, 4, '0', STR_PAD_LEFT);
            
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
            
            // Process each item
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
                'message' => 'Failed to cancel transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a draft transfer.
     */
    public function destroy(StockTransfer $stockTransfer): JsonResponse
    {
        try {
            if ($stockTransfer->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft transfers can be deleted'
                ], 422);
            }
            
            $stockTransfer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Transfer deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Transfer deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transfer'
            ], 500);
        }
    }

    /**
     * Generate transfer report.
     */
    public function report(Request $request): JsonResponse
    {
        try {
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
                    'incoming' => $transfers->groupBy('to_warehouse_id')->map(function($items) {
                        return $items->sum('total_cost');
                    }),
                    'outgoing' => $transfers->groupBy('from_warehouse_id')->map(function($items) {
                        return $items->sum('total_cost');
                    })
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'transfers' => $transfers,
                    'summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report'
            ], 500);
        }
    }
}