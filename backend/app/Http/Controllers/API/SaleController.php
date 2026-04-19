<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SaleCollection;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Refund;
use App\Models\SaleReturn;
use App\Services\StockService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    use AuthorizesRequests;

    protected StockService $stockService;
    protected AccountingService $accountingService;

    public function __construct(StockService $stockService, AccountingService $accountingService)
    {
        $this->stockService = $stockService;
        $this->accountingService = $accountingService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(): SaleCollection
    {
        $sales = Sale::with(['customer', 'user', 'warehouse'])
            ->latest()
            ->paginate(15);

        return new SaleCollection($sales);
    }

    /**
     * Store a newly created sale.
     */
    /**
 * Store a newly created sale (Simplified for testing).
 */
public function store(StoreSaleRequest $request)
{
    try {
        Log::info('Sale store started', $request->all());
        
        return DB::transaction(function () use ($request) {
            $subtotalTotal = 0;
            $totalCogs = 0;
            
            // Create Sale Header
            $sale = Sale::create([
                'customer_id'    => $request->customer_id,
                'warehouse_id'   => $request->warehouse_id,
                'created_by'     => Auth::id(),
                'sale_date'      => $request->sale_date,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'discount'       => $request->discount ?? 0,
                'tax'            => $request->tax ?? 0,
                'total_amount'   => 0,
                'total_cogs'     => 0,
                'gross_profit'   => 0,
            ]);
            
            Log::info('Sale created', ['sale_id' => $sale->id]);
            
            // Process Each Item
            foreach ($request->items as $item) {
                $costPrice = $item['cost_price'] ?? 100; // Temporary default
                $quantity = $item['quantity'];
                $sellingPrice = $item['selling_price'];
                
                $subtotal = $quantity * $sellingPrice;
                $cogs = $quantity * $costPrice;
                
                $subtotalTotal += $subtotal;
                $totalCogs += $cogs;
                
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $quantity,
                    'selling_price' => $sellingPrice,
                    'cost_price'    => $costPrice,
                    'subtotal'      => $subtotal,
                    'gross_profit'  => $subtotal - $cogs,
                ]);
            }
            
            // Calculate Final Total
            $finalTotal = $subtotalTotal - ($request->discount ?? 0) + ($request->tax ?? 0);
            
            // Update Sale Header
            $sale->update([
                'total_amount' => $finalTotal,
                'total_cogs'   => $totalCogs,
                'gross_profit' => $subtotalTotal - $totalCogs,
            ]);
            
            // Return simple response without relationships first
            return response()->json([
                'message' => 'Sale created successfully',
                'id' => $sale->id,
                'sale' => [
                    'id' => $sale->id,
                    'total_amount' => $finalTotal,
                    'items_count' => count($request->items)
                ]
            ], 201);
        });
    } catch (\Exception $e) {
        Log::error('Sale creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'Failed to create sale: ' . $e->getMessage()
        ], 500);
    }
}
    
   public function show(Sale $sale): SaleResource
    {
        // Load ALL needed relationships explicitly to prevent lazy loading
        $sale->load([
            'customer',
            'user', 
            'warehouse',
            'items.product',
            'returns.product',
            'returns.refund',
            'returns.processedBy'
        ]);
        
        return new SaleResource($sale);
    }

    /**
     * Update the specified sale.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): JsonResponse
    {
        try {
            $this->validateSaleModifiable($sale);

            return DB::transaction(function () use ($request, $sale) {
                // Delete existing journal entries for this sale
                $this->accountingService->deleteEntry('sale', $sale->id);
                
                // Restore stock from old items
                foreach ($sale->items as $oldItem) {
                    $this->stockService->increaseStock(
                        $oldItem->product_id,
                        $sale->warehouse_id,
                        $oldItem->quantity,
                        $oldItem->cost_price,
                        'sale_update_restore',
                        $sale->id,
                        Auth::id()
                    );
                }

                // Delete old items
                $sale->items()->delete();

                $subtotalTotal = 0;
                $totalCogs = 0;
                $totalGrossProfit = 0;

                // Insert new items & decrease stock again
                foreach ($request->items as $item) {
                    $costPrice = $this->stockService->getAverageCost(
                        $item['product_id'],
                        $sale->warehouse_id
                    );

                    $quantity = $item['quantity'];
                    $sellingPrice = $item['selling_price'];

                    $subtotal = $quantity * $sellingPrice;
                    $cogs = $quantity * $costPrice;
                    $grossProfit = $subtotal - $cogs;

                    $subtotalTotal += $subtotal;
                    $totalCogs += $cogs;
                    $totalGrossProfit += $grossProfit;

                    SaleItem::create([
                        'sale_id'       => $sale->id,
                        'product_id'    => $item['product_id'],
                        'quantity'      => $quantity,
                        'selling_price' => $sellingPrice,
                        'cost_price'    => $costPrice,
                        'subtotal'      => $subtotal,
                        'gross_profit'  => $grossProfit,
                    ]);

                    $this->stockService->decreaseStock(
                        $item['product_id'],
                        $sale->warehouse_id,
                        $quantity,
                        $costPrice,
                        'sale_update',
                        $sale->id,
                        Auth::id()
                    );
                }

                // Recalculate total
                $finalTotal = $subtotalTotal
                    - ($request->discount ?? 0)
                    + ($request->tax ?? 0);

                // Update sale header
                $sale->update([
                    'customer_id'    => $request->customer_id,
                    'sale_date'      => $request->sale_date,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_status,
                    'discount'       => $request->discount ?? 0,
                    'tax'            => $request->tax ?? 0,
                    'total_amount'   => $finalTotal,
                    'total_cogs'     => $totalCogs,
                    'gross_profit'   => $totalGrossProfit,
                ]);

                // Post new financial journal entry
                $lines = [
                    [
                        'account_id' => $this->getPaymentAccountId($request->payment_method), 
                        'debit' => $finalTotal, 
                        'credit' => 0
                    ],
                    [
                        'account_id' => config('accounts.sales_revenue', 2), 
                        'debit' => 0, 
                        'credit' => $subtotalTotal
                    ],
                    [
                        'account_id' => config('accounts.cogs', 3), 
                        'debit' => $totalCogs, 
                        'credit' => 0
                    ],
                    [
                        'account_id' => config('accounts.inventory', 4), 
                        'debit' => 0, 
                        'credit' => $totalCogs
                    ],
                ];

                if ($request->tax > 0) {
                    $lines[] = [
                        'account_id' => config('accounts.tax_payable', 5), 
                        'debit' => 0, 
                        'credit' => $request->tax
                    ];
                }

                if ($request->discount > 0) {
                    $lines[] = [
                        'account_id' => config('accounts.sales_discount', 6), 
                        'debit' => $request->discount, 
                        'credit' => 0
                    ];
                }

                $this->accountingService->createEntry(
                    date: $sale->sale_date,
                    description: "Sales Invoice #{$sale->id} (Updated)",
                    lines: $lines,
                    referenceType: 'sale',
                    referenceId: $sale->id
                );

                // Load relationships for response
                $sale->load(['customer', 'user', 'warehouse']);
                $sale->load(['items' => function($query) {
                    $query->with('product');
                }]);

                return response()->json([
                    'message' => 'Sale updated successfully',
                    'sale' => new SaleResource($sale)
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified sale.
     */
    public function destroy(Sale $sale): JsonResponse
    {
        try {
            $this->validateSaleModifiable($sale);

            return DB::transaction(function () use ($sale) {
                // Delete journal entries
                $this->accountingService->deleteEntry('sale', $sale->id);
                
                // Restore stock for all items
                foreach ($sale->items as $item) {
                    $this->stockService->increaseStock(
                        $item->product_id,
                        $sale->warehouse_id,
                        $item->quantity,
                        $item->cost_price,
                        'sale_delete_restore',
                        $sale->id,
                        Auth::id()
                    );
                }

                // Delete sale (cascade deletes items)
                $sale->delete();

                return response()->json([
                    'message' => 'Sale deleted successfully.',
                    'sale_id' => $sale->id
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF receipt for sale.
     */
    /**
 * Generate PDF receipt for sale.
 */
public function receipt(Sale $sale)
{
    $sale->load([
        'items.product', 
        'customer', 
        'user', 
        'warehouse'
    ]);
    
    $pdf = Pdf::loadView('receipts.sale', [
        'sale' => $sale,
        'company' => [
            'name'    => config('app.name', 'ShopSync'),
            'address' => config('app.address', '123 Business Ave, Commercial District'),
            'phone'   => config('app.phone', '+880 1234 567890'),
            'email'   => config('app.email', 'info@shopsync.com'),
            'tax_id'  => config('app.tax_id', 'TAX-123456789'),
        ]
    ]);
    
    // Set paper size and orientation
    $pdf->setPaper('A4', 'portrait');
    
    // Return for download or stream
    return $pdf->stream("invoice_{$sale->id}.pdf");
}
    /**
     * Process a return for a sale item.
     */
    public function returnItem(Request $request, Sale $sale): JsonResponse
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,wallet',
            'reason'         => 'required|string|max:255',
        ]);

        try {
            return DB::transaction(function () use ($request, $sale) {
                // Check if sale is modifiable
                $this->validateSaleModifiable($sale);

                // Prepare return using StockService
                $return = $this->stockService->prepareSaleReturn(
                    $sale,
                    $request->product_id,
                    $request->quantity,
                    Auth::id(),
                    $request->reason
                );

                $refundAmount = $return->refund_amount;
                $approvalThreshold = config('pos.return_approval_threshold', 100);

                // Auto-approve if under threshold, otherwise mark as pending
                if ($refundAmount <= $approvalThreshold) {
                    $this->approveReturn($return, Auth::id());
                    $message = 'Return processed successfully';
                } else {
                    $return->update(['status' => 'pending']);
                    $message = 'Return submitted for approval';
                }

                // Load relationships carefully
                $return->load(['product', 'processedBy']);
                $sale->load(['returns' => function($query) {
                    $query->with(['product', 'refund']);
                }]);

                return response()->json([
                    'message' => $message,
                    'return'  => $return,
                    'sale'    => new SaleResource($sale)
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF receipt for return.
     */
    public function returnReceipt(SaleReturn $return)
    {
        $return->load([
            'sale.customer',
            'sale.warehouse',
            'product',
            'processedBy',
            'refund'
        ]);

        $pdf = Pdf::loadView('receipts.return', [
            'return' => $return,
            'company' => [
                'name'    => config('app.name'),
                'address' => config('app.address', 'Your Company Address'),
                'phone'   => config('app.phone', 'Your Phone'),
                'email'   => config('app.email', 'your@email.com'),
                'tax_id'  => config('app.tax_id', 'Your Tax ID'),
            ]
        ]);

        return $pdf->download("return_receipt_{$return->id}.pdf");
    }

    /**
     * Approve a pending return.
     */
    protected function approveReturn(SaleReturn $return, $managerId = null): SaleReturn
    {
        return DB::transaction(function () use ($return, $managerId) {
            $approvedBy = $managerId ?? Auth::id();

            // Update return status
            $return->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now()
            ]);

            // Finalize stock (this already handles inventory ledger)
            $this->stockService->finalizeSaleReturn($return);

            // Create refund record
            $refund = Refund::create([
                'sale_return_id' => $return->id,
                'payment_method' => $return->payment_method,
                'amount'         => $return->refund_amount,
                'processed_by'   => $approvedBy,
            ]);

            // Create accounting entry for the refund
            $cogsAmount = $this->calculateReturnCogs($return);
            
            $lines = [
                [
                    'account_id' => config('accounts.sales_returns', 9), 
                    'debit' => $return->refund_amount, 
                    'credit' => 0
                ],
                [
                    'account_id' => $this->getPaymentAccountId($return->payment_method), 
                    'debit' => 0, 
                    'credit' => $return->refund_amount
                ],
            ];

            // Add inventory/COGS reversal if there's a cost
            if ($cogsAmount > 0) {
                $lines[] = [
                    'account_id' => config('accounts.inventory', 4), 
                    'debit' => $cogsAmount, 
                    'credit' => 0
                ];
                $lines[] = [
                    'account_id' => config('accounts.cogs', 3), 
                    'debit' => 0, 
                    'credit' => $cogsAmount
                ];
            }

            $this->accountingService->createEntry(
                date: now(),
                description: "Sales Return for Invoice #{$return->sale_id} - Product #{$return->product_id}",
                lines: $lines,
                referenceType: 'sale_return',
                referenceId: $return->id
            );

            return $return;
        });
    }

    /**
     * API endpoint for approving returns.
     */
    public function approve(SaleReturn $return): JsonResponse
    {
        try {
            $this->authorize('approve', $return);

            $this->approveReturn($return);

            $return->load(['product', 'refund']);

            return response()->json([
                'message' => 'Return approved successfully',
                'return' => $return
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate COGS for returned items.
     */
    private function calculateReturnCogs(SaleReturn $return): float
    {
        $saleItem = SaleItem::where('sale_id', $return->sale_id)
            ->where('product_id', $return->product_id)
            ->first();

        return $saleItem ? ($saleItem->cost_price * $return->quantity) : 0;
    }

    /**
     * Validate if sale can be modified.
     *
     * @throws \Exception
     */
    private function validateSaleModifiable(Sale $sale): void
    {
        if ($sale->payment_status === 'paid') {
            throw new \Exception('Paid sales cannot be modified.');
        }

        // Check if sale has any approved returns
        if ($sale->returns()->where('status', 'approved')->exists()) {
            throw new \Exception('Sales with approved returns cannot be modified.');
        }
    }

    /**
     * Get the appropriate account ID based on payment method.
     */
    private function getPaymentAccountId(string $paymentMethod): int
    {
        return match($paymentMethod) {
            'cash' => config('accounts.cash', 1),
            'card' => config('accounts.bank', 7),
            'wallet' => config('accounts.mobile_wallet', 8),
            default => config('accounts.accounts_receivable', 9),
        };
    }

    /**
 * Get recently sold products
 */
    public function recentProducts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $recentProducts = SaleItem::with('product')
            ->select('product_id', DB::raw('MAX(created_at) as last_sold'))
            ->groupBy('product_id')
            ->orderBy('last_sold', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return $item->product;
            });
        
        return response()->json([
            'success' => true,
            'data' => $recentProducts
        ]);
    }
}