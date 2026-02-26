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
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Request;


class SaleController extends Controller
{
    use AuthorizesRequests;

    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
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

                // Get average cost (COGS base)
                $costPrice = $this->stockService->getAverageCost(
                    $item['product_id'],
                    $sale->warehouse_id
                );

                // Calculations
                $quantity = $item['quantity'];
                $sellingPrice = $item['selling_price'];

                $subtotal = $quantity * $sellingPrice;
                $cogs = $quantity * $costPrice;
                $grossProfit = $subtotal - $cogs;

                $subtotalTotal += $subtotal;
                $totalCogs += $cogs;
                $totalGrossProfit += $grossProfit;

                // Create Sale Item
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $quantity,
                    'selling_price' => $sellingPrice,
                    'cost_price'    => $costPrice,
                    'subtotal'      => $subtotal,
                    'gross_profit'  => $grossProfit,
                ]);

                // 3️⃣ Decrease Stock (Correct Parameter Order)
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

            // 4️⃣ Calculate Final Total
            $finalTotal = $subtotalTotal
                - ($request->discount ?? 0)
                + ($request->tax ?? 0);

            // 5️⃣ Update Sale Header with Financial Data
            $sale->update([
                'total_amount' => $finalTotal,
                'total_cogs'   => $totalCogs,
                'gross_profit' => $totalGrossProfit,
            ]);

            // 6️⃣ Load relationships for receipt
            $sale->load(['customer', 'items.product', 'user', 'warehouse']);

            // 7️⃣ Generate Receipt PDF
            $pdf = Pdf::loadView('receipts.sale', [
                'sale' => $sale,
                'company' => [
                    'name'    => config('app.name'),
                    'address' => config('app.address', 'Your Company Address'),
                    'phone'   => config('app.phone', 'Your Phone'),
                    'email'   => config('app.email', 'your@email.com'),
                    'tax_id'  => config('app.tax_id', 'Your Tax ID'),
                ]
            ]);

            return $pdf->download("receipt_{$sale->id}.pdf");
        });

    } catch (\Exception $e) {

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
            // Authorization is handled by UpdateSaleRequest
            $this->validateSaleModifiable($sale);

            return DB::transaction(function () use ($request, $sale) {
                // 1️⃣ Restore stock from old items
                foreach ($sale->items as $oldItem) {
                $this->stockService->increaseStock(
                    $oldItem->product_id,
                    $sale->warehouse_id,
                    $oldItem->quantity,
                    $oldItem->selling_price,
                    'sale_update_restore',
                    $sale->id,
                    Auth::id()
                );
                }

                // 2️⃣ Delete old items
                $sale->items()->delete();

                $subtotalTotal = 0;

                // 3️⃣ Insert new items & decrease stock again
                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['selling_price'];
                    $subtotalTotal += $subtotal;

                    SaleItem::create([
                        'sale_id'       => $sale->id,
                        'product_id'    => $item['product_id'],
                        'quantity'      => $item['quantity'],
                        'selling_price' => $item['selling_price'],
                        'subtotal'      => $subtotal,
                    ]);

                        $this->stockService->decreaseStock(
                        $item['product_id'],
                        $sale->warehouse_id,
                        $item['quantity'],
                        $item['selling_price'],
                        'sale_update',
                        $sale->id,
                        Auth::id()
                        );
                }

                // 4️⃣ Recalculate total
                $finalTotal = $subtotalTotal
                    - ($request->discount ?? 0)
                    + ($request->tax ?? 0);

                // 5️⃣ Update sale header
                $sale->update([
                    'customer_id'    => $request->customer_id,
                    'sale_date'      => $request->sale_date,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_status,
                    'discount'       => $request->discount ?? 0,
                    'tax'            => $request->tax ?? 0,
                    'total_amount'   => $finalTotal,
                ]);

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
                // 1️⃣ Restore stock for all items
                foreach ($sale->items as $item) {
                    $this->stockService->increaseStock(
                $item->product_id,
                $sale->warehouse_id,
                $item->quantity,
                $item->selling_price,
                'sale_delete_restore',
                $sale->id,
                Auth::id()
                );
                }

                // 2️⃣ Delete sale (cascade deletes items)
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
        ]);

        DB::transaction(function () use ($request, $sale) {

            $return = $this->stockService->processSaleReturn(
                $sale,
                $request->product_id,
                $request->quantity,
                Auth::id()
            );

            // Create Refund Record
            Refund::create([
                'sale_return_id' => $return->id,
                'payment_method' => $request->payment_method,
                'amount'         => $request->quantity * $return->cost_price,
                'processed_by'   => Auth::id(),
            ]);
        });

        $sale->load([
            'customer',
            'warehouse',
            'items.product',
            'returns.product',
            'returns.refund' // eager load
        ]);

        return response()->json([
            'message' => 'Return processed successfully',
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

    
}