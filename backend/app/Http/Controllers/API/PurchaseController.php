<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\PurchaseCollection;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use App\Services\StockService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
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
     * GET /api/purchases
     */
    public function index(Request $request): PurchaseCollection
    {
        $query = Purchase::with(['supplier', 'warehouse', 'user', 'items.product']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('purchase_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $purchases = $query->latest()->paginate($request->per_page ?? 15);

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
                $paidAmount = (float) ($request->paid_amount ?? 0);

                // 1️⃣ Create Purchase
                $purchase = Purchase::create([
                    'supplier_id'    => $request->supplier_id,
                    'warehouse_id'   => $request->warehouse_id,
                    'user_id'        => Auth::id(),
                    'purchase_date'  => $request->purchase_date ?? now(),
                    'reference_no'   => $this->generateReferenceNumber(),
                    'total_amount'   => 0, // Will update after items
                    'paid_amount'    => $paidAmount,
                    'payment_status' => 'unpaid', // Will update after total calculation
                    'status'         => $request->status ?? 'ordered',
                ]);

                // 2️⃣ Create Purchase Items + Update Stock
                foreach ($request->items as $item) {
                    $quantity = (float) $item['quantity'];
                    $purchasePrice = (float) $item['purchase_price'];
                    $subtotal = $quantity * $purchasePrice;
                    
                    // Calculate discount and tax if provided
                    $discountAmount = ($subtotal * ((float) ($item['discount'] ?? 0))) / 100;
                    $taxAmount = (($subtotal - $discountAmount) * ((float) ($item['tax'] ?? 0))) / 100;
                    $itemTotal = $subtotal - $discountAmount + $taxAmount;
                    
                    $totalAmount += $itemTotal;

                    PurchaseItem::create([
                        'purchase_id'    => $purchase->id,
                        'product_id'     => $item['product_id'],
                        'quantity'       => $quantity,
                        'purchase_price' => $purchasePrice,
                        'discount'       => (float) ($item['discount'] ?? 0),
                        'tax'            => (float) ($item['tax'] ?? 0),
                        'subtotal'       => $subtotal,
                        'total'          => $itemTotal,
                    ]);

                    // 📦 Increase stock if status is 'received'
                    if ($request->status === 'received') {
                        $this->stockService->increaseStock(
                            $item['product_id'],
                            $request->warehouse_id,
                            $quantity,
                            $purchasePrice,
                            'purchase',
                            $purchase->id,
                            Auth::id()
                        );
                    }
                }

                // 3️⃣ Update total amount and payment status
                $paymentStatus = $this->determinePaymentStatus($paidAmount, $totalAmount);
                
                $purchase->update([
                    'total_amount'   => $totalAmount,
                    'payment_status' => $paymentStatus
                ]);

                // 4️⃣ Post to Accounting if status is 'received'
                if ($request->status === 'received') {
                    $this->postToAccounting($purchase, (float) $totalAmount);
                }

                return new PurchaseResource(
                    $purchase->load(['supplier', 'warehouse', 'user', 'items.product'])
                );
            });
        } catch (\Exception $e) {
            Log::error('Purchase creation failed: ' . $e->getMessage());
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
            $purchase->load(['supplier', 'warehouse', 'user', 'items.product.unit'])
        );
    }

    /**
     * PUT/PATCH /api/purchases/{id}
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): PurchaseResource|JsonResponse
    {
        try {
            $this->validatePurchaseModifiable($purchase);

            return DB::transaction(function () use ($request, $purchase) {
                $totalAmount = 0;
                $oldStatus = $purchase->status;

                // 1️⃣ Reverse previous stock if it was received
                if ($oldStatus === 'received') {
                    foreach ($purchase->items as $item) {
                        $this->stockService->decreaseStock(
                            $item->product_id,
                            $purchase->warehouse_id,
                            (float) $item->quantity,
                            $item->purchase_price,
                            'purchase_update_reverse',
                            $purchase->id,
                            Auth::id()
                        );
                    }
                }

                // 2️⃣ Delete old items
                $purchase->items()->delete();

                // 3️⃣ Create new items
                foreach ($request->items as $item) {
                    $quantity = (float) $item['quantity'];
                    $purchasePrice = (float) $item['purchase_price'];
                    $subtotal = $quantity * $purchasePrice;
                    
                    $discountAmount = ($subtotal * ((float) ($item['discount'] ?? 0))) / 100;
                    $taxAmount = (($subtotal - $discountAmount) * ((float) ($item['tax'] ?? 0))) / 100;
                    $itemTotal = $subtotal - $discountAmount + $taxAmount;
                    
                    $totalAmount += $itemTotal;

                    PurchaseItem::create([
                        'purchase_id'    => $purchase->id,
                        'product_id'     => $item['product_id'],
                        'quantity'       => $quantity,
                        'purchase_price' => $purchasePrice,
                        'discount'       => (float) ($item['discount'] ?? 0),
                        'tax'            => (float) ($item['tax'] ?? 0),
                        'subtotal'       => $subtotal,
                        'total'          => $itemTotal,
                    ]);

                    // 📦 Increase stock if new status is 'received'
                    if ($request->status === 'received') {
                        $this->stockService->increaseStock(
                            $item['product_id'],
                            $request->warehouse_id ?? $purchase->warehouse_id,
                            $quantity,
                            $purchasePrice,
                            'purchase_update',
                            $purchase->id,
                            Auth::id()
                        );
                    }
                }

                // 4️⃣ Determine payment status
                $paidAmount = (float) ($request->paid_amount ?? $purchase->paid_amount);
                $paymentStatus = $this->determinePaymentStatus($paidAmount, $totalAmount);

                // 5️⃣ Update purchase
                $purchase->update([
                    'supplier_id'     => $request->supplier_id,
                    'warehouse_id'    => $request->warehouse_id ?? $purchase->warehouse_id,
                    'purchase_date'   => $request->purchase_date ?? $purchase->purchase_date,
                    'total_amount'    => $totalAmount,
                    'paid_amount'     => $paidAmount,
                    'payment_status'  => $paymentStatus,
                    'status'          => $request->status ?? $purchase->status,
                ]);

                // 6️⃣ Handle accounting if status changed to received
                if ($request->status === 'received' && $oldStatus !== 'received') {
                    $this->postToAccounting($purchase, (float) $totalAmount);
                }

                return new PurchaseResource(
                    $purchase->load(['supplier', 'warehouse', 'user', 'items.product'])
                );
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to update this purchase'], 403);
        } catch (\Exception $e) {
            Log::error('Purchase update failed: ' . $e->getMessage());
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
                // 📦 Reverse stock if purchase was received
                if ($purchase->status === 'received') {
                    foreach ($purchase->items as $item) {
                        $this->stockService->decreaseStock(
                            $item->product_id,
                            $purchase->warehouse_id,
                            (float) $item->quantity,
                            $item->purchase_price,
                            'purchase_delete',
                            $purchase->id,
                            Auth::id()
                        );
                    }
                }

                // Delete purchase items first (though cascading should handle it)
                $purchase->items()->delete();
                
                // Delete purchase
                $purchase->delete();

                return response()->json([
                    'message'      => 'Purchase deleted successfully.',
                    'purchase_id'  => $purchase->id
                ]);
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized to delete this purchase'], 403);
        } catch (\Exception $e) {
            Log::error('Purchase deletion failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/purchases/{id}/receive
     */
    public function receive(Purchase $purchase): JsonResponse
    {
        try {
            if ($purchase->status === 'received') {
                return response()->json(['message' => 'Purchase already received'], 400);
            }

            return DB::transaction(function () use ($purchase) {
                // Update stock for all items
                foreach ($purchase->items as $item) {
                    $this->stockService->increaseStock(
                        $item->product_id,
                        $purchase->warehouse_id,
                        (float) $item->quantity,
                        (float) $item->purchase_price,
                        'purchase_receive',
                        $purchase->id,
                        Auth::id()
                    );
                }

                // Update purchase status
                $purchase->update(['status' => 'received']);

                // Post to accounting - FIX: Cast to float
                $this->postToAccounting($purchase, (float) $purchase->total_amount);

                return response()->json([
                    'message' => 'Purchase received successfully',
                    'purchase' => new PurchaseResource($purchase->load(['supplier', 'warehouse', 'user', 'items.product']))
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Purchase receive failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to receive purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/purchases/{id}/payments
     */
    public function addPayment(Request $request, Purchase $purchase): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        try {
            return DB::transaction(function () use ($validated, $purchase) {
                $paymentAmount = (float) $validated['amount'];
                $newPaidAmount = (float) $purchase->paid_amount + $paymentAmount;
                $totalAmount = (float) $purchase->total_amount;
                
                if ($newPaidAmount > $totalAmount) {
                    return response()->json([
                        'message' => 'Payment amount exceeds total amount'
                    ], 400);
                }

                // Determine new payment status
                $paymentStatus = $this->determinePaymentStatus($newPaidAmount, $totalAmount);

                // Update purchase with new paid amount and status
                $purchase->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $paymentStatus
                ]);

                // Update accounting entries
                $this->updateAccountingForPayment($purchase, $paymentAmount);

                return response()->json([
                    'message' => 'Payment added successfully',
                    'purchase' => new PurchaseResource($purchase->load(['supplier', 'warehouse']))
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Payment addition failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to add payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/purchases/{id}/items
     */
    public function getItems(Purchase $purchase): JsonResponse
    {
        return response()->json([
            'items' => $purchase->items()->with('product.unit')->get()
        ]);
    }

    /**
     * Helper Methods
     */
    private function determinePaymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($totalAmount == 0) {
            return 'unpaid';
        }
        
        if ($paidAmount >= $totalAmount) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial';
        }
        return 'unpaid';
    }

    private function generateReferenceNumber(): string
    {
        $prefix = 'PO-';
        $year = date('Y');
        $month = date('m');
        
        $lastPurchase = Purchase::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->first();

        if ($lastPurchase && preg_match('/(\d+)$/', $lastPurchase->reference_no, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . $newNumber;
    }

    private function validatePurchaseModifiable(Purchase $purchase): void
    {
        if ($purchase->payment_status === 'paid') {
            throw new \Exception('Paid purchases cannot be modified.');
        }
    }

    private function postToAccounting(Purchase $purchase, float $totalAmount): void
    {
        // Get account IDs from config or use defaults
        $inventoryAccountId = config('accounts.inventory', 4); // Default to 4 if not set
        $accountsPayableId = config('accounts.accounts_payable', 5); // Default to 5 if not set

        // Post to Accounting: Debit Inventory, Credit Accounts Payable
        $this->accountingService->createEntry(
            $purchase->purchase_date,
            "Purchase Invoice #{$purchase->reference_no}",
            [
                ['account_id' => $inventoryAccountId, 'debit' => $totalAmount, 'credit' => 0], // Inventory Asset
                ['account_id' => $accountsPayableId, 'debit' => 0, 'credit' => $totalAmount], // Accounts Payable
            ],
            'purchase',
            $purchase->id
        );
    }

    private function updateAccountingForPayment(Purchase $purchase, float $paymentAmount): void
    {
        // Get account IDs from config or use defaults
        $accountsPayableId = config('accounts.accounts_payable', 5);
        $cashAccountId = config('accounts.cash', 1); // Default to 1 if not set

        // Post payment entry: Debit Accounts Payable, Credit Cash/Bank
        $this->accountingService->createEntry(
            now(),
            "Payment for Purchase #{$purchase->reference_no}",
            [
                ['account_id' => $accountsPayableId, 'debit' => $paymentAmount, 'credit' => 0],
                ['account_id' => $cashAccountId, 'debit' => 0, 'credit' => $paymentAmount],
            ],
            'purchase_payment',
            $purchase->id
        );
    }
}