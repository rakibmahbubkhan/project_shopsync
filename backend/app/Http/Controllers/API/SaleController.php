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
        $sales = Sale::with(['customer', 'user'])
            ->latest()
            ->paginate(15);

        return new SaleCollection($sales);
    }

    /**
     * Store a newly created sale.
     */
    public function store(StoreSaleRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                $subtotalTotal = 0;
                $totalCogs = 0;
                $totalGrossProfit = 0;

                // 1️⃣ Create Sale Header
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

                // 2️⃣ Process Each Item
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
                        'sale',
                        $sale->id,
                        Auth::id()
                    );
                }

                // 3️⃣ Calculate Final Total
                $finalTotal = $subtotalTotal
                    - ($request->discount ?? 0)
                    + ($request->tax ?? 0);

                // 4️⃣ Update Sale Header with Financial Data
                $sale->update([
                    'total_amount' => $finalTotal,
                    'total_cogs'   => $totalCogs,
                    'gross_profit' => $totalGrossProfit,
                ]);

                // 5️⃣ Post Financial Journal Entry
                $lines = [
                    // Debit: Cash or Accounts Receivable
                    ['account_id' => $this->getPaymentAccountId($request->payment_method), 'debit' => $finalTotal, 'credit' => 0],
                    
                    // Credit: Sales Revenue
                    ['account_id' => config('accounts.sales_revenue', 2), 'debit' => 0, 'credit' => $subtotalTotal],
                    
                    // Debit: COGS
                    ['account_id' => config('accounts.cogs', 3), 'debit' => $totalCogs, 'credit' => 0],
                    
                    // Credit: Inventory Asset
                    ['account_id' => config('accounts.inventory', 4), 'debit' => 0, 'credit' => $totalCogs],
                ];

                // Add tax line if applicable
                if ($request->tax > 0) {
                    $lines[] = ['account_id' => config('accounts.tax_payable', 5), 'debit' => 0, 'credit' => $request->tax];
                }

                // Add discount line if applicable
                if ($request->discount > 0) {
                    $lines[] = ['account_id' => config('accounts.sales_discount', 6), 'debit' => $request->discount, 'credit' => 0];
                }

                $this->accountingService->createEntry(
                    date: $sale->sale_date,
                    description: "Sales Invoice #{$sale->id}",
                    lines: $lines,
                    referenceType: 'sale',
                    referenceId: $sale->id
                );

                // 6️⃣ Load relationships for the response
                $sale->load(['customer', 'items.product', 'user', 'warehouse']);

                return response()->json([
                    'message' => 'Sale created successfully',
                    'id'      => $sale->id,
                    'sale'    => new SaleResource($sale)
                ], 201);

            });

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create sale: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified sale.
     */
    public function show(Sale $sale): SaleResource
    {
        return new SaleResource(
            $sale->load([
                'customer',
                'user',
                'warehouse',
                'items.product',
                'returns.product',   
            ])
        );
    }

    /**
     * Update the specified sale.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): SaleResource|JsonResponse
    {
        try {
            $this->validateSaleModifiable($sale);

            return DB::transaction(function () use ($request, $sale) {
                // 1️⃣ Delete existing journal entries for this sale
                // You'll need to implement this method in AccountingService
                $this->accountingService->deleteEntry('sale', $sale->id);

                // 2️⃣ Restore stock from old items using COST PRICE, not selling price
                foreach ($sale->items as $oldItem) {
                    $this->stockService->increaseStock(
                        $oldItem->product_id,
                        $sale->warehouse_id,
                        $oldItem->quantity,
                        $oldItem->cost_price, // FIXED: Use cost_price instead of selling_price
                        'sale_update_restore',
                        $sale->id,
                        Auth::id()
                    );
                }

                // 3️⃣ Delete old items
                $sale->items()->delete();

                $subtotalTotal = 0;
                $totalCogs = 0;
                $totalGrossProfit = 0;

                // 4️⃣ Insert new items & decrease stock again
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

                // 5️⃣ Recalculate total
                $finalTotal = $subtotalTotal
                    - ($request->discount ?? 0)
                    + ($request->tax ?? 0);

                // 6️⃣ Update sale header
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

                // 7️⃣ Post new financial journal entry
                $lines = [
                    ['account_id' => $this->getPaymentAccountId($request->payment_method), 'debit' => $finalTotal, 'credit' => 0],
                    ['account_id' => config('accounts.sales_revenue', 2), 'debit' => 0, 'credit' => $subtotalTotal],
                    ['account_id' => config('accounts.cogs', 3), 'debit' => $totalCogs, 'credit' => 0],
                    ['account_id' => config('accounts.inventory', 4), 'debit' => 0, 'credit' => $totalCogs],
                ];

                if ($request->tax > 0) {
                    $lines[] = ['account_id' => config('accounts.tax_payable', 5), 'debit' => 0, 'credit' => $request->tax];
                }

                if ($request->discount > 0) {
                    $lines[] = ['account_id' => config('accounts.sales_discount', 6), 'debit' => $request->discount, 'credit' => 0];
                }

                $this->accountingService->createEntry(
                    date: $sale->sale_date,
                    description: "Sales Invoice #{$sale->id} (Updated)",
                    lines: $lines,
                    referenceType: 'sale',
                    referenceId: $sale->id
                );

                return new SaleResource(
                    $sale->load(['customer', 'items.product'])
                );
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to update this sale'], 403);
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
            $this->authorize('delete', $sale);
            $this->validateSaleModifiable($sale);

            return DB::transaction(function () use ($sale) {
                // 1️⃣ Delete journal entries
                $this->accountingService->deleteEntry('sale', $sale->id);
                // 2️⃣ Restore stock for all items using COST PRICE
                foreach ($sale->items as $item) {
                    $this->stockService->increaseStock(
                        $item->product_id,
                        $sale->warehouse_id,
                        $item->quantity,
                        $item->cost_price, // FIXED: Use cost_price instead of selling_price
                        'sale_delete_restore',
                        $sale->id,
                        Auth::id()
                    );
                }

                // 3️⃣ Delete sale (cascade deletes items)
                $sale->delete();

                return response()->json([
                    'message' => 'Sale deleted successfully.',
                    'sale_id' => $sale->id
                ]);
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to delete this sale'], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete sale: ' . $e->getMessage()
            ], 500);
        }
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
    }

    /**
     * Get the appropriate account ID based on payment method.
     */
    private function getPaymentAccountId(string $paymentMethod): int
    {
        return match($paymentMethod) {
            'cash' => config('accounts.cash', 1),
            'card' => config('accounts.accounts_receivable', 7),
            'wallet' => config('accounts.wallet', 8),
            default => config('accounts.accounts_receivable', 7),
        };
    }

    public function receipt(Sale $sale)
    {
        $sale->load('items.product');

        $pdf = Pdf::loadView('receipts.sale', [
            'sale' => $sale
        ]);

        return $pdf->stream("receipt_{$sale->id}.pdf");
    }

    public function returnItem(Request $request, Sale $sale)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,wallet',
            'reason'         => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $return = $this->stockService->prepareSaleReturn(
                $sale,
                $request->product_id,
                $request->quantity,
                Auth::id(),
                $request->reason
            );

            $refundAmount = $return->refund_amount;

            if ($refundAmount > config('pos.return_approval_threshold', 100)) {
                $return->update([
                    'status' => 'pending'
                ]);
            } else {
                $this->approveReturn($return, Auth::id());
            }
        });

        $sale->load(['returns.product', 'returns.refund']);

        return response()->json([
            'message' => 'Return submitted',
            'sale' => new SaleResource($sale)
        ]);
    }

    public function returnReceipt(SaleReturn $return)
    {
        $return->load([
            'sale.customer',
            'sale.warehouse',
            'product',
            'processedBy'
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

    public function approveReturn(SaleReturn $return, $managerId)
    {
        DB::transaction(function () use ($return, $managerId) {
            // Update return status
            $return->update([
                'status' => 'approved',
                'approved_by' => $managerId,
                'approved_at' => now()
            ]);

            // Finalize stock (this already handles inventory ledger)
            $this->stockService->finalizeSaleReturn($return);

            // Create refund record
            Refund::create([
                'sale_return_id' => $return->id,
                'payment_method' => $return->payment_method,
                'amount'         => $return->refund_amount,
                'processed_by'   => $managerId,
            ]);

            // Create accounting entry for the refund
            $cogsAmount = $this->calculateReturnCogs($return);
            
            $lines = [
                // Debit: Sales Returns/Allowances
                ['account_id' => config('accounts.sales_returns', 9), 'debit' => $return->refund_amount, 'credit' => 0],
                // Credit: Cash/Accounts Receivable
                ['account_id' => $this->getPaymentAccountId($return->payment_method), 'debit' => 0, 'credit' => $return->refund_amount],
            ];

            // Add inventory/COGS reversal if there's a cost
            if ($cogsAmount > 0) {
                $lines[] = ['account_id' => config('accounts.inventory', 4), 'debit' => $cogsAmount, 'credit' => 0];
                $lines[] = ['account_id' => config('accounts.cogs', 3), 'debit' => 0, 'credit' => $cogsAmount];
            }

            $this->accountingService->createEntry(
                date: now(),
                description: "Sales Return for Invoice #{$return->sale_id} - Product #{$return->product_id}",
                lines: $lines,
                referenceType: 'sale_return',
                referenceId: $return->id
            );
        });
    }

    private function calculateReturnCogs(SaleReturn $return): float
    {
        $saleItem = SaleItem::where('sale_id', $return->sale_id)
            ->where('product_id', $return->product_id)
            ->first();

        return $saleItem ? ($saleItem->cost_price * $return->quantity) : 0;
    }

    public function approve(SaleReturn $return)
    {
        if (!Auth::user()->can('approve_return')) {
            abort(403);
        }

        DB::transaction(function () use ($return) {
            $this->approveReturn($return, Auth::id());
        });

        return response()->json(['message' => 'Return approved']);
    }
}