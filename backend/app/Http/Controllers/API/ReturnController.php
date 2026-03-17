<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
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
     * Process a return for a specific sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $sale = Sale::with('items')->findOrFail($validated['sale_id']);
            $returnTotalAmount = 0;
            $returnTotalCogs = 0;

            // 1. Create Return Header
            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'user_id' => Auth::id(),
                'return_date' => now(),
                'reason' => $validated['reason'],
                'status' => 'completed',
                'total_amount' => 0, // Updated later
            ]);

            foreach ($validated['items'] as $itemData) {
                $saleItem = $sale->items->where('product_id', $itemData['product_id'])->first();
                
                if (!$saleItem || $itemData['quantity'] > $saleItem->quantity) {
                    throw new \Exception("Invalid return quantity for product ID: {$itemData['product_id']}");
                }

                $itemRefundAmount = $itemData['quantity'] * $saleItem->selling_price;
                $itemCogsReversal = $itemData['quantity'] * $saleItem->cost_price;
                
                $returnTotalAmount += $itemRefundAmount;
                $returnTotalCogs += $itemCogsReversal;

                // 2. Physical Stock Restoration
                $this->stockService->increaseStock(
                    $itemData['product_id'],
                    $sale->warehouse_id,
                    $itemData['quantity'],
                    $saleItem->cost_price,
                    'sale_return',
                    $saleReturn->id,
                    Auth::id()
                );
            }

            $saleReturn->update(['total_amount' => $returnTotalAmount]);

            // 3. Create Refund Record
            Refund::create([
                'sale_return_id' => $saleReturn->id,
                'amount' => $returnTotalAmount,
                'payment_method' => $sale->payment_method,
                'status' => 'completed',
                'processed_by' => Auth::id(),
            ]);

            // 4. Post Accounting Reversal Entries
            // Debit: Sales Revenue (Reduce Revenue), Credit: Cash/Bank (Refund Money)
            // Debit: Inventory (Restock), Credit: COGS (Reduce Expense)
            $this->accountingService->createEntry(
                date: now()->toDateString(),
                description: "Return Reversal for Sale #{$sale->id}",
                lines: [
                    ['account_id' => 2, 'debit' => $returnTotalAmount, 'credit' => 0], // Revenue
                    ['account_id' => 1, 'debit' => 0, 'credit' => $returnTotalAmount], // Cash/Bank
                    ['account_id' => 4, 'debit' => $returnTotalCogs, 'credit' => 0],   // Inventory
                    ['account_id' => 3, 'debit' => 0, 'credit' => $returnTotalCogs],   // COGS
                ],
                referenceType: 'sale_return',
                referenceId: $saleReturn->id
            );

            return response()->json($saleReturn, 201);
        });
    }
}