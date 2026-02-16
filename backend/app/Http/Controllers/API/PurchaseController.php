<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\PurchaseCollection;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * GET /api/purchases
     */
    public function index(): PurchaseCollection
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->latest()
            ->paginate(15);

        return new PurchaseCollection($purchases);
    }

    /**
     * POST /api/purchases
     */
    public function store(StorePurchaseRequest $request): PurchaseResource|JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $totalAmount = 0;

                // 1️⃣ Create Purchase
                $purchase = Purchase::create([
                    'supplier_id'    => $request->supplier_id,
                    'created_by'     => auth()->id(),
                    'purchase_date'  => $request->purchase_date,
                    'payment_status' => $request->payment_status,
                    'total_amount'   => 0
                ]);

                // 2️⃣ Create Purchase Items + Update Stock
                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['cost_price'];
                    $totalAmount += $subtotal;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id'  => $item['product_id'],
                        'quantity'    => $item['quantity'],
                        'cost_price'  => $item['cost_price'],
                        'subtotal'    => $subtotal,
                    ]);

                    // 📦 Increase stock (Purchase adds stock)
                    $this->stockService->increaseStock(
                        $item['product_id'],
                        $item['quantity'],
                        'purchase',
                        $purchase->id,
                        auth()->id()
                    );
                }

                // 3️⃣ Update total amount
                $purchase->update(['total_amount' => $totalAmount]);

                return new PurchaseResource(
                    $purchase->load(['supplier', 'items.product'])
                );
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/purchases/{id}
     */
    public function show(Purchase $purchase): PurchaseResource
    {
        return new PurchaseResource(
            $purchase->load(['supplier', 'user', 'items.product'])
        );
    }

    /**
     * PUT/PATCH /api/purchases/{id}
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): PurchaseResource|JsonResponse
    {
        try {
            // Authorization handled by UpdatePurchaseRequest
            $this->validatePurchaseModifiable($purchase);

            return DB::transaction(function () use ($request, $purchase) {
                // 1️⃣ Reverse previous stock (decrease since purchase added stock)
                foreach ($purchase->items as $oldItem) {
                    $this->stockService->decreaseStock(
                        $oldItem->product_id,
                        $oldItem->quantity,
                        'purchase_update_reverse',
                        $purchase->id,
                        auth()->id()
                    );
                }

                // 2️⃣ Delete old items
                $purchase->items()->delete();

                $totalAmount = 0;

                // 3️⃣ Reinsert new items & increase stock
                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['cost_price'];
                    $totalAmount += $subtotal;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id'  => $item['product_id'],
                        'quantity'    => $item['quantity'],
                        'cost_price'  => $item['cost_price'],
                        'subtotal'    => $subtotal,
                    ]);

                    // 📦 Increase stock with new quantities
                    $this->stockService->increaseStock(
                        $item['product_id'],
                        $item['quantity'],
                        'purchase_update',
                        $purchase->id,
                        auth()->id()
                    );
                }

                // 4️⃣ Update purchase header
                $purchase->update([
                    'supplier_id'    => $request->supplier_id,
                    'purchase_date'  => $request->purchase_date,
                    'payment_status' => $request->payment_status,
                    'total_amount'   => $totalAmount
                ]);

                return new PurchaseResource(
                    $purchase->load(['supplier', 'items.product'])
                );
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to update this purchase'], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/purchases/{id}
     */
    public function destroy(Purchase $purchase): JsonResponse
    {
        try {
            $this->authorize('delete', $purchase);
            $this->validatePurchaseModifiable($purchase);

            return DB::transaction(function () use ($purchase) {
                // 📦 Reverse stock for all items (decrease since purchase added stock)
                foreach ($purchase->items as $item) {
                    $this->stockService->decreaseStock(
                        $item->product_id,
                        $item->quantity,
                        'purchase_delete',
                        $purchase->id,
                        auth()->id()
                    );
                }

                // Delete purchase (cascade deletes items)
                $purchase->delete();

                return response()->json([
                    'message'      => 'Purchase deleted successfully.',
                    'purchase_id'  => $purchase->id
                ]);
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to delete this purchase'], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate if purchase can be modified.
     *
     * @throws \Exception
     */
    private function validatePurchaseModifiable(Purchase $purchase): void
    {
        if ($purchase->payment_status === 'paid') {
            throw new \Exception('Paid purchases cannot be modified.');
        }
    }
}