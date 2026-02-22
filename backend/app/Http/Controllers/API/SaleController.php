<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SaleCollection;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


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

                // 1️⃣ Create Sale Header
                $sale = Sale::create([
                    'customer_id'    => $request->customer_id,
                    'created_by'     => Auth::id(),
                    'sale_date'      => $request->sale_date,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_status,
                    'discount'       => $request->discount ?? 0,
                    'tax'            => $request->tax ?? 0,
                    'total_amount'   => 0
                ]);

                // 2️⃣ Insert Items & Decrease Stock
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

                    // Decrease stock (will throw if insufficient)
                    $this->stockService->decreaseStock(
                        $item['product_id'],
                        $item['quantity'],
                        'sale',
                        $sale->id,
                        auth()->id()
                    );
                }

                // 3️⃣ Calculate Final Total
                $finalTotal = $subtotalTotal 
                    - ($request->discount ?? 0) 
                    + ($request->tax ?? 0);

                $sale->update(['total_amount' => $finalTotal]);

                // 4️⃣ Load relationships for receipt
                $sale->load(['customer', 'items.product', 'user']);

                // 5️⃣ Generate and return PDF
                $pdf = PDF::loadView('receipts.sale', [
                    'sale' => $sale,
                    'company' => [
                        'name' => config('app.name'),
                        'address' => config('app.address', 'Your Company Address'),
                        'phone' => config('app.phone', 'Your Phone'),
                        'email' => config('app.email', 'your@email.com'),
                        'tax_id' => config('app.tax_id', 'Your Tax ID')
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
            $sale->load(['customer', 'user', 'items.product'])
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
                        $oldItem->quantity,
                        'sale',
                        $sale->id,
                        auth()->id()
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
                        $item['quantity'],
                        'sale',
                        $sale->id,
                        auth()->id()
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
                        $item->quantity,
                        'sale',
                        $sale->id,
                        auth()->id()
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
}