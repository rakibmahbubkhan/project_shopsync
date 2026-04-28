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
            'sale.customer', 
            'items.product',
            'refund',
            'user'
        ]);

        // Search by sale ID or customer name
        if ($request->search) {
            $query->whereHas('sale', function ($q) use ($request) {
                $q->where('id', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function ($sq) use ($request) {
                      $sq->where('name', 'like', "%{$request->search}%");
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

        return response()->json(
            $query->latest()->paginate($request->per_page ?? 15)
        );
    }

    /**
     * Show a specific return
     */
    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load([
            'sale.customer',
            'sale.warehouse',
            'sale.items.product',
            'items.product',
            'refund',
            'user',
            'approvedBy'
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
        $query = Sale::with(['customer', 'warehouse', 'items.product']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                  });
            });
        }

        // Only show sales that have items to return
        $query->whereHas('items');

        return response()->json(
            $query->latest('sale_date')
                  ->limit(20)
                  ->get()
                  ->map(function ($sale) {
                      return [
                          'id' => $sale->id,
                          'sale_date' => $sale->sale_date,
                          'total_amount' => $sale->total_amount,
                          'paid_amount' => $sale->paid_amount,
                          'payment_status' => $sale->payment_status,
                          'customer' => $sale->customer ? [
                              'id' => $sale->customer->id,
                              'name' => $sale->customer->name,
                              'mobile_number' => $sale->customer->mobile_number
                          ] : null,
                          'warehouse' => $sale->warehouse ? [
                              'id' => $sale->warehouse->id,
                              'name' => $sale->warehouse->name
                          ] : null,
                          'items' => $sale->items->map(function ($item) {
                              $alreadyReturned = SaleReturnItem::whereHas('saleReturn', function ($q) use ($item) {
                                  $q->where('sale_id', $item->sale_id)
                                    ->whereIn('status', ['approved', 'completed']);
                              })->where('product_id', $item->product_id)->sum('quantity');

                              return [
                                  'id' => $item->id,
                                  'product_id' => $item->product_id,
                                  'product_name' => $item->product->name ?? 'N/A',
                                  'product_sku' => $item->product->sku ?? 'N/A',
                                  'quantity' => $item->quantity,
                                  'already_returned' => $alreadyReturned,
                                  'available_for_return' => $item->quantity - $alreadyReturned,
                                  'selling_price' => $item->selling_price,
                                  'cost_price' => $item->cost_price,
                                  'subtotal' => $item->subtotal
                              ];
                          })->filter(function ($item) {
                              return $item['available_for_return'] > 0;
                          })->values()
                      ];
                  })
        );
    }

    /**
     * Process a new return
     */
    public function store(Request $request)
    {
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

        return DB::transaction(function () use ($validated) {
            $sale = Sale::with('items.product')->findOrFail($validated['sale_id']);
            $returnTotalAmount = 0;
            $returnTotalCogs = 0;

            // Create Return Header
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
                    throw new \Exception("Return quantity ({$itemData['quantity']}) exceeds available quantity ({$availableQty}) for product: {$saleItem->product->name}");
                }

                // Calculate amounts
                $itemRefundAmount = $itemData['quantity'] * $saleItem->selling_price;
                $itemCogsReversal = $itemData['quantity'] * $saleItem->cost_price;
                
                // Apply discount if any
                $itemDiscount = ($itemData['discount'] ?? 0) * $itemData['quantity'];
                $itemRefundAmount -= $itemDiscount;
                
                // Apply tax if any
                $itemTax = ($itemData['tax'] ?? 0) * $itemData['quantity'];
                $itemRefundAmount += $itemTax;

                $returnTotalAmount += $itemRefundAmount;
                $returnTotalCogs += $itemCogsReversal;

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
            $profitReversal = $returnTotalAmount - $returnTotalCogs;
            $sale->decrement('total_amount', $returnTotalAmount);
            $sale->decrement('total_cogs', $returnTotalCogs);
            $sale->decrement('gross_profit', $profitReversal);
            
            // Update paid amount if fully paid
            if ($sale->payment_status === 'paid') {
                $sale->decrement('paid_amount', $returnTotalAmount);
                $sale->updatePaymentStatus();
            }

            // Post Accounting Reversal Entries
            $this->accountingService->createEntry(
                date: now()->toDateString(),
                description: "Return for Sale #{$sale->id} - Refund: {$returnTotalAmount}",
                lines: [
                    ['account_id' => config('accounts.sales_returns', 9), 'debit' => $returnTotalAmount, 'credit' => 0],
                    ['account_id' => $this->getPaymentAccountId($validated['refund_method']), 'debit' => 0, 'credit' => $returnTotalAmount],
                    ['account_id' => config('accounts.inventory', 4), 'debit' => $returnTotalCogs, 'credit' => 0],
                    ['account_id' => config('accounts.cogs', 3), 'debit' => 0, 'credit' => $returnTotalCogs],
                ],
                referenceType: 'sale_return',
                referenceId: $saleReturn->id
            );

            return response()->json([
                'message' => 'Return processed successfully',
                'data' => $saleReturn->load(['items.product', 'refund'])
            ], 201);
        });
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