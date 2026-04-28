<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Refund;
use App\Services\StockService;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    protected StockService $stockService;
    protected AccountingService $accountingService;

    public function __construct(StockService $stockService, AccountingService $accountingService)
    {
        $this->stockService = $stockService;
        $this->accountingService = $accountingService;
    }

    /**
     * List all returns with filters
     */
    public function index(Request $request)
    {
        $query = SaleReturn::with([
            'sale:id,customer_id,total_amount,paid_amount,payment_status,sale_date',
            'sale.customer:id,name,mobile_number',
            'items:id,sale_return_id,product_id,quantity,selling_price,subtotal',
            'items.product:id,name,sku',
            'refund:id,sale_return_id,amount,payment_method,status',
            'user:id,name'
        ]);

        // Search by return ID, sale ID or customer name
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

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        // Sort
        switch ($request->sort_by) {
            case 'oldest':
                $query->oldest('return_date');
                break;
            case 'highest':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'lowest':
                $query->orderBy('total_amount', 'asc');
                break;
            default:
                $query->latest('return_date');
                break;
        }

        $returns = $query->paginate($request->per_page ?? 15);

        return response()->json($returns);
    }

    /**
     * Show a specific return
     */
    public function show(SaleReturn $saleReturn)
    {
        // Eager load only needed relationships with specific columns
        $saleReturn->load([
            'sale:id,customer_id,warehouse_id,total_amount,sale_date,payment_status',
            'sale.customer:id,name,mobile_number',
            'sale.warehouse:id,name',
            'items.product:id,name,sku',
            'refund',
            'user:id,name',
            'approvedBy:id,name'
        ]);

        return response()->json([
            'data' => $saleReturn
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

        $query = Sale::with([
            'customer:id,name,mobile_number',
            'warehouse:id,name',
            'items' => function ($q) {
                $q->select('id', 'sale_id', 'product_id', 'quantity', 'selling_price', 'cost_price', 'subtotal');
            },
            'items.product:id,name,sku'
        ]);

        $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($sq) use ($search) {
                  $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
              });
        });

        $query->whereHas('items');

        $sales = $query->latest('sale_date')
                      ->limit(20)
                      ->get();

        // Map to array to avoid circular references
        $result = $sales->map(function ($sale) {
            // Calculate already returned quantities
            $alreadyReturned = SaleReturnItem::whereHas('saleReturn', function ($q) use ($sale) {
                $q->where('sale_id', $sale->id)
                  ->whereIn('status', ['approved', 'completed']);
            })->select('product_id', DB::raw('SUM(quantity) as total_returned'))
              ->groupBy('product_id')
              ->pluck('total_returned', 'product_id');

            return [
                'id' => $sale->id,
                'sale_date' => $sale->sale_date ? $sale->sale_date->toDateTimeString() : null,
                'total_amount' => (float) $sale->total_amount,
                'paid_amount' => (float) ($sale->paid_amount ?? 0),
                'payment_status' => $sale->payment_status,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'mobile_number' => $sale->customer->mobile_number ?? ''
                ] : null,
                'warehouse' => $sale->warehouse ? [
                    'id' => $sale->warehouse->id,
                    'name' => $sale->warehouse->name
                ] : null,
                'items' => $sale->items->map(function ($item) use ($alreadyReturned) {
                    $returned = (int) ($alreadyReturned[$item->product_id] ?? 0);
                    $available = $item->quantity - $returned;
                    
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'N/A',
                        'product_sku' => $item->product->sku ?? 'N/A',
                        'quantity' => (int) $item->quantity,
                        'already_returned' => $returned,
                        'available_for_return' => max(0, $available),
                        'selling_price' => (float) $item->selling_price,
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'subtotal' => (float) $item->subtotal
                    ];
                })->filter(function ($item) {
                    return $item['available_for_return'] > 0;
                })->values()->toArray()
            ];
        });

        return response()->json($result);
    }

    /**
     * Process a new return
     */
    public function store(Request $request)
    {
        try {
            Log::info('Return store started', $request->all());

            $validated = $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.tax' => 'nullable|numeric|min:0',
                'reason' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'refund_method' => 'required|string|in:cash,card,bank_transfer,mobile_banking',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                // Load sale with items (select only needed columns)
                $sale = Sale::with([
                    'items' => function ($q) {
                        $q->select('id', 'sale_id', 'product_id', 'quantity', 'selling_price', 'cost_price', 'subtotal');
                    },
                    'items.product:id,name,sku'
                ])->select('id', 'customer_id', 'warehouse_id', 'total_amount', 'paid_amount', 
                           'total_cogs', 'gross_profit', 'payment_status', 'sale_date')
                  ->findOrFail($validated['sale_id']);

                $returnTotalAmount = 0;

                // Create Return Header - use only scalar values
                $saleReturn = SaleReturn::create([
                    'sale_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'return_date' => now(),
                    'reason' => $validated['reason'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'completed',
                    'total_amount' => 0,
                ]);

                foreach ($validated['items'] as $itemData) {
                    $saleItem = $sale->items->where('product_id', $itemData['product_id'])->first();
                    
                    if (!$saleItem) {
                        throw new \Exception("Product ID {$itemData['product_id']} not found in this sale");
                    }

                    // Check already returned quantity
                    $alreadyReturned = SaleReturnItem::whereHas('saleReturn', function ($q) use ($sale) {
                        $q->where('sale_id', $sale->id)
                          ->whereIn('status', ['approved', 'completed']);
                    })->where('product_id', $itemData['product_id'])->sum('quantity');

                    $availableQty = $saleItem->quantity - $alreadyReturned;

                    if ($itemData['quantity'] > $availableQty) {
                        throw new \Exception(
                            "Return quantity ({$itemData['quantity']}) exceeds available quantity ({$availableQty}) " .
                            "for product: " . ($saleItem->product->name ?? 'Unknown')
                        );
                    }

                    // Calculate amounts
                    $itemRefundAmount = $itemData['quantity'] * $saleItem->selling_price;
                    
                    // Apply discount if any
                    $itemDiscount = ($itemData['discount'] ?? 0) * $itemData['quantity'];
                    $itemRefundAmount -= $itemDiscount;
                    
                    // Apply tax if any
                    $itemTax = ($itemData['tax'] ?? 0) * $itemData['quantity'];
                    $itemRefundAmount += $itemTax;

                    $returnTotalAmount += $itemRefundAmount;

                    // Create return item
                    SaleReturnItem::create([
                        'sale_return_id' => $saleReturn->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'selling_price' => $saleItem->selling_price,
                        'cost_price' => $saleItem->cost_price,
                        'subtotal' => $itemRefundAmount,
                        'discount' => $itemDiscount,
                        'tax' => $itemTax,
                    ]);

                    // Restore Stock to warehouse
                    try {
                        $this->stockService->increaseStock(
                            $itemData['product_id'],
                            $sale->warehouse_id,
                            $itemData['quantity'],
                            $saleItem->cost_price,
                            'sale_return',
                            $saleReturn->id,
                            Auth::id(),
                            "Return from Sale #{$sale->id}"
                        );
                    } catch (\Exception $e) {
                        Log::error('Stock restoration failed', [
                            'product_id' => $itemData['product_id'],
                            'error' => $e->getMessage()
                        ]);
                        throw new \Exception("Failed to restore stock: " . $e->getMessage());
                    }
                }

                // Update return total
                $saleReturn->update(['total_amount' => $returnTotalAmount]);

                // Create Refund Record
                Refund::create([
                    'sale_return_id' => $saleReturn->id,
                    'amount' => $returnTotalAmount,
                    'payment_method' => $validated['refund_method'],
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                ]);

                // Update Sale totals
                $profitReversal = $returnTotalAmount - 0; // Simplified
                $sale->decrement('total_amount', $returnTotalAmount);
                if ($sale->payment_status === 'paid') {
                    $sale->decrement('paid_amount', min($returnTotalAmount, $sale->paid_amount));
                    $sale->updatePaymentStatus();
                }

                Log::info('Return processed successfully', [
                    'return_id' => $saleReturn->id,
                    'amount' => $returnTotalAmount
                ]);

                // Return simple response without nested relationships to avoid recursion
                return response()->json([
                    'message' => 'Return processed successfully',
                    'data' => [
                        'id' => $saleReturn->id,
                        'sale_id' => $saleReturn->sale_id,
                        'total_amount' => (float) $returnTotalAmount,
                        'status' => $saleReturn->status,
                        'reason' => $saleReturn->reason,
                        'return_date' => $saleReturn->return_date ? $saleReturn->return_date->toDateTimeString() : null,
                        'items' => collect($validated['items'])->map(function ($item) {
                            return [
                                'product_id' => $item['product_id'],
                                'quantity' => $item['quantity']
                            ];
                        }),
                        'refund' => [
                            'amount' => (float) $returnTotalAmount,
                            'payment_method' => $validated['refund_method'],
                            'status' => 'completed'
                        ]
                    ]
                ], 201);

            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Return validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Return processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

        try {
            return DB::transaction(function () use ($saleReturn) {
                $saleReturn->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Create refund if not exists
                if ($saleReturn->refund()->count() === 0) {
                    Refund::create([
                        'sale_return_id' => $saleReturn->id,
                        'amount' => $saleReturn->total_amount,
                        'payment_method' => 'cash',
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
                    'data' => [
                        'id' => $saleReturn->id,
                        'status' => $saleReturn->status
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Return approval failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to approve return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a return
     */
    public function reject(SaleReturn $saleReturn)
    {
        if ($saleReturn->status !== 'pending') {
            return response()->json(['message' => 'Only pending returns can be rejected'], 400);
        }

        $saleReturn->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Return rejected',
            'data' => [
                'id' => $saleReturn->id,
                'status' => $saleReturn->status
            ]
        ]);
    }

    /**
     * Get the appropriate account ID based on payment method.
     */
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