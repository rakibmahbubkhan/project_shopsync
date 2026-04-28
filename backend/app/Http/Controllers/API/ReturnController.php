<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Refund;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ReturnController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * List all returns with filters
     */
    public function index(Request $request)
    {
        // Use select to limit columns and prevent deep loading
        $query = SaleReturn::select([
            'id', 'sale_id', 'user_id', 'return_date', 'reason', 
            'total_amount', 'status', 'notes', 'approved_by', 'approved_at',
            'created_at', 'updated_at'
        ])->with([
            'sale:id,customer_id,total_amount,paid_amount,payment_status,sale_date',
            'sale.customer:id,name,mobile_number',
            'items' => function($q) {
                $q->select('id', 'sale_return_id', 'product_id', 'quantity', 'selling_price', 'subtotal');
            },
            'items.product:id,name,sku',
            'refund:id,sale_return_id,amount,payment_method,status',
            'user:id,name'
        ]);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('sale_id', 'like', "%{$search}%")
                  ->orWhereHas('sale.customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        switch ($request->sort_by) {
            case 'oldest': $query->oldest('return_date'); break;
            case 'highest': $query->orderBy('total_amount', 'desc'); break;
            case 'lowest': $query->orderBy('total_amount', 'asc'); break;
            default: $query->latest('return_date'); break;
        }

        $returns = $query->paginate($request->per_page ?? 15);
        
        // Convert to array to break any circular references
        $responseData = $returns->toArray();
        
        return response()->json($responseData);
    }

    /**
     * Show a specific return
     */
    public function show($id)
    {
        $saleReturn = SaleReturn::select([
            'id', 'sale_id', 'user_id', 'return_date', 'reason',
            'total_amount', 'status', 'notes', 'approved_by', 'approved_at',
            'created_at', 'updated_at'
        ])->with([
            'sale:id,customer_id,warehouse_id,total_amount,sale_date,payment_status',
            'sale.customer:id,name,mobile_number',
            'sale.warehouse:id,name',
            'items' => function($q) {
                $q->select('id', 'sale_return_id', 'product_id', 'quantity', 'selling_price', 'cost_price', 'subtotal');
            },
            'items.product:id,name,sku',
            'refund:id,sale_return_id,amount,payment_method,status,processed_by',
            'user:id,name',
            'approvedBy:id,name'
        ])->findOrFail($id);

        return response()->json([
            'data' => $saleReturn->toArray()
        ]);
    }

    /**
     * Search sales for return processing
     */
    public function searchSales(Request $request)
    {
        $search = $request->search;
        
        if (!$search || strlen(trim($search)) < 2) {
            return response()->json([]);
        }

        // Use a raw query approach to avoid model serialization issues
        $sales = Sale::select('id', 'customer_id', 'warehouse_id', 'sale_date', 
                              'total_amount', 'paid_amount', 'payment_status')
            ->with([
                'customer:id,name,mobile_number',
                'warehouse:id,name',
                'items:id,sale_id,product_id,quantity,selling_price,cost_price,subtotal',
                'items.product:id,name,sku'
            ])
            ->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                  });
            })
            ->whereHas('items')
            ->latest('sale_date')
            ->limit(20)
            ->get();

        // Convert to plain arrays immediately to break Eloquent recursion
        $result = [];
        foreach ($sales as $sale) {
            $items = [];
            foreach ($sale->items as $item) {
                $items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_sku' => $item->product->sku ?? 'N/A',
                    'quantity' => (int) $item->quantity,
                    'already_returned' => 0,
                    'available_for_return' => (int) $item->quantity,
                    'selling_price' => (float) $item->selling_price,
                    'cost_price' => (float) ($item->cost_price ?? 0),
                    'subtotal' => (float) $item->subtotal,
                ];
            }

            $result[] = [
                'id' => $sale->id,
                'sale_date' => $sale->sale_date ? $sale->sale_date->toDateTimeString() : null,
                'total_amount' => (float) $sale->total_amount,
                'paid_amount' => (float) ($sale->paid_amount ?? 0),
                'payment_status' => $sale->payment_status,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'mobile_number' => $sale->customer->mobile_number ?? '',
                ] : null,
                'warehouse' => $sale->warehouse ? [
                    'id' => $sale->warehouse->id,
                    'name' => $sale->warehouse->name,
                ] : null,
                'items' => $items,
            ];
        }

        return response()->json($result);
    }

    /**
     * Process a new return
     */
    public function store(Request $request)
    {
        try {
            Log::info('Return store started', [
                'sale_id' => $request->sale_id,
                'items_count' => count($request->items ?? []),
                'user_id' => Auth::id()
            ]);

            $validated = $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'reason' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'refund_method' => 'required|string|in:cash,card,bank_transfer,mobile_banking',
            ]);

            // Get sale with minimal data using query builder to avoid model serialization
            $saleData = DB::table('sales')
                ->where('id', $validated['sale_id'])
                ->first(['id', 'customer_id', 'warehouse_id', 'total_amount', 
                         'paid_amount', 'total_cogs', 'gross_profit', 'payment_status']);

            if (!$saleData) {
                return response()->json(['message' => 'Sale not found'], 404);
            }

            // Get sale items directly from database
            $saleItems = DB::table('sale_items')
                ->where('sale_id', $validated['sale_id'])
                ->get(['id', 'product_id', 'quantity', 'selling_price', 'cost_price', 'subtotal']);

            $returnTotalAmount = 0;
            $returnItemsData = [];
            $stockItemData = [];

            // Validate and calculate each return item
            foreach ($validated['items'] as $itemData) {
                $saleItem = $saleItems->where('product_id', $itemData['product_id'])->first();
                
                if (!$saleItem) {
                    throw new \Exception("Product ID {$itemData['product_id']} not found in this sale");
                }

                // Check already returned quantity
                $alreadyReturned = DB::table('sale_return_items')
                    ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
                    ->where('sale_returns.sale_id', $validated['sale_id'])
                    ->whereIn('sale_returns.status', ['approved', 'completed'])
                    ->where('sale_return_items.product_id', $itemData['product_id'])
                    ->sum('sale_return_items.quantity');

                $availableQty = $saleItem->quantity - $alreadyReturned;

                if ($itemData['quantity'] > $availableQty) {
                    throw new \Exception(
                        "Return quantity ({$itemData['quantity']}) exceeds available quantity ({$availableQty})"
                    );
                }

                $itemRefundAmount = $itemData['quantity'] * $saleItem->selling_price;
                $returnTotalAmount += $itemRefundAmount;

                $returnItemsData[] = [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'selling_price' => $saleItem->selling_price,
                    'cost_price' => $saleItem->cost_price,
                    'subtotal' => $itemRefundAmount,
                    'discount' => 0,
                    'tax' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stockItemData[] = [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $saleItem->cost_price,
                ];
            }

            // Execute everything in transaction using DB facade entirely
            DB::beginTransaction();
            
            try {
                // 1. Create return record
                $returnId = DB::table('sale_returns')->insertGetId([
                    'sale_id' => $validated['sale_id'],
                    'user_id' => Auth::id(),
                    'return_date' => now(),
                    'reason' => $validated['reason'],
                    'total_amount' => $returnTotalAmount,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Insert return items
                foreach ($returnItemsData as &$item) {
                    $item['sale_return_id'] = $returnId;
                }
                DB::table('sale_return_items')->insert($returnItemsData);

                // 3. Create refund
                DB::table('refunds')->insert([
                    'sale_return_id' => $returnId,
                    'amount' => $returnTotalAmount,
                    'payment_method' => $validated['refund_method'],
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Restore stock for each item
                foreach ($stockItemData as $stockItem) {
                    $this->stockService->increaseStock(
                        $stockItem['product_id'],
                        $saleData->warehouse_id,
                        $stockItem['quantity'],
                        $stockItem['cost_price'],
                        'sale_return',
                        $returnId,
                        Auth::id(),
                        "Return from Sale #{$validated['sale_id']}"
                    );
                }

                // 5. Update sale totals
                DB::table('sales')
                    ->where('id', $validated['sale_id'])
                    ->decrement('total_amount', $returnTotalAmount);

                if ($saleData->payment_status === 'paid') {
                    DB::table('sales')
                        ->where('id', $validated['sale_id'])
                        ->decrement('paid_amount', min($returnTotalAmount, $saleData->paid_amount));
                }

                DB::commit();

                Log::info('Return processed successfully', [
                    'return_id' => $returnId,
                    'sale_id' => $validated['sale_id'],
                    'amount' => $returnTotalAmount
                ]);

                return response()->json([
                    'message' => 'Return processed successfully',
                    'data' => [
                        'id' => $returnId,
                        'sale_id' => (int) $validated['sale_id'],
                        'total_amount' => (float) $returnTotalAmount,
                        'status' => 'completed',
                        'reason' => $validated['reason'],
                        'return_date' => now()->toDateTimeString(),
                        'items' => array_map(function($item) {
                            return [
                                'product_id' => $item['product_id'],
                                'quantity' => $item['quantity']
                            ];
                        }, $returnItemsData),
                        'refund' => [
                            'amount' => (float) $returnTotalAmount,
                            'payment_method' => $validated['refund_method'],
                            'status' => 'completed'
                        ]
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Return transaction failed', ['error' => $e->getMessage()]);
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Return processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a pending return
     */
    public function approve(SaleReturn $saleReturn)
    {
        if ($saleReturn->status !== 'pending') {
            return response()->json(['message' => 'Only pending returns can be approved'], 400);
        }

        return DB::transaction(function () use ($saleReturn) {
            $saleReturn->approve(Auth::id());

            // Process refund if not already done
            if ($saleReturn->refund()->count() === 0) {
                Refund::create([
                    'sale_return_id' => $saleReturn->id,
                    'amount' => $saleReturn->total_amount,
                    'payment_method' => 'cash', // Can be updated
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                ]);
            }

            // Restore stock for each item
            foreach ($saleReturn->items as $item) {
                $this->stockService->increaseStock(
                    $item->product_id,
                    $saleReturn->sale->warehouse_id,
                    $item->quantity,
                    $item->cost_price,
                    'sale_return_approved',
                    $saleReturn->id,
                    Auth::id(),
                    "Approved return from Sale #{$saleReturn->sale_id}"
                );
            }

            return response()->json([
                'message' => 'Return approved successfully',
                'data' => $saleReturn->fresh(['items.product', 'refund'])
            ]);
        });
    }

    /**
     * Reject a return
     */
    public function reject(SaleReturn $saleReturn)
    {
        if ($saleReturn->status !== 'pending') {
            return response()->json(['message' => 'Only pending returns can be rejected'], 400);
        }

        $saleReturn->reject();

        return response()->json([
            'message' => 'Return rejected',
            'data' => $saleReturn
        ]);
    }

    private function getPaymentAccountId(string $paymentMethod): int
    {
        return match($paymentMethod) {
            'cash' => config('accounts.cash', 1),
            'card' => config('accounts.bank', 7),
            'bank_transfer' => config('accounts.bank', 7),
            'mobile_banking' => config('accounts.mobile_wallet', 8),
            default => config('accounts.accounts_receivable', 9),
        };
    }
}