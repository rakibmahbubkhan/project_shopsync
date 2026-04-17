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
use App\Models\Supplier;
use App\Models\PurchasePayment; 
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
        
        // Load additional relationships for each purchase
        $purchases->getCollection()->each(function ($purchase) {
            $purchase->load(['supplier', 'warehouse', 'items.product', 'payments']);
        });

        return new PurchaseCollection($purchases);
    }

    /**
     * POST /api/purchases
     */
    public function store(StorePurchaseRequest $request): PurchaseResource|JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $subtotal = (float) $request->subtotal ?? 0;
                $totalDiscount = (float) $request->total_discount ?? 0;
                $totalTax = (float) $request->total_tax ?? 0;
                $totalAmount = (float) $request->total_amount ?? 0;
                $paidAmount = (float) ($request->paid_amount ?? 0);
                $shippingCost = (float) ($request->shipping_cost ?? 0);
                
                // Calculate final total with shipping
                $finalTotal = $totalAmount + $shippingCost;
                
                // Determine payment status
                $paymentStatus = $this->determinePaymentStatus($paidAmount, $finalTotal);

                // 1️⃣ Create Purchase with all fields
                $purchase = Purchase::create([
                    'supplier_id' => $request->supplier_id,
                    'warehouse_id' => $request->warehouse_id,
                    'purchase_date' => $request->purchase_date ?? now(),
                    'reference_no' => $this->generateReferenceNumber(),
                    'subtotal' => $subtotal,
                    'total_discount' => $totalDiscount,
                    'total_tax' => $totalTax,
                    'total_amount' => $finalTotal,
                    'paid_amount' => $paidAmount,
                    'payment_status' => $paymentStatus,
                    'status' => $request->status ?? 'ordered',
                    'notes' => $request->notes ?? null,
                    'shipping_method' => $request->shipping_method ?? null,
                    'shipping_cost' => $shippingCost,
                    'payment_method' => $request->payment_method ?? null,
                    'expected_delivery_date' => $request->expected_delivery_date ?? null,
                    'delivered_date' => $request->status === 'received' ? now() : null,
                    'created_by' => Auth::id(),
                ]);

                // 2️⃣ Create Purchase Items
                foreach ($request->items as $item) {
                    $quantity = (float) $item['quantity'];
                    $purchasePrice = (float) $item['purchase_price'];
                    $itemSubtotal = $quantity * $purchasePrice;
                    
                    // Calculate discount and tax
                    $discountPercent = (float) ($item['discount_percent'] ?? 0);
                    $discountAmount = ($itemSubtotal * $discountPercent) / 100;
                    
                    $taxPercent = (float) ($item['tax_percent'] ?? 0);
                    $taxableAmount = $itemSubtotal - $discountAmount;
                    $taxAmount = ($taxableAmount * $taxPercent) / 100;
                    
                    $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'purchase_price' => $purchasePrice,
                        'subtotal' => $itemSubtotal,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'tax_percent' => $taxPercent,
                        'tax_amount' => $taxAmount,
                        'total' => $itemTotal,
                        'batch_no' => $item['batch_no'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'notes' => $item['notes'] ?? null,
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

                // 3️⃣ Create initial payment if paid_amount > 0
                if ($paidAmount > 0) {
                    PurchasePayment::create([
                        'purchase_id' => $purchase->id,
                        'amount' => $paidAmount,
                        'payment_date' => $request->purchase_date ?? now(),
                        'payment_method' => $request->payment_method ?? 'cash',
                        'reference_no' => $request->payment_reference_no ?? null,
                        'notes' => 'Initial payment',
                        'installment_number' => 1,
                        'created_by' => Auth::id(),
                    ]);
                }

                // 4️⃣ Post to Accounting if status is 'received'
                if ($request->status === 'received') {
                    $this->postToAccounting($purchase, (float) $finalTotal);
                }

                return new PurchaseResource(
                    $purchase->load(['supplier', 'warehouse', 'user', 'items.product', 'payments'])
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
/**
 * GET /api/purchases/{id}
 */
    public function show(Purchase $purchase): JsonResponse
    {
        try {
            // Load all necessary relationships
            $purchase->load([
                'supplier', 
                'warehouse', 
                'user', 
                'items.product',
                'payments'
            ]);
            
            // Return as JSON directly instead of using Resource
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'supplier' => $purchase->supplier,
                    'warehouse_id' => $purchase->warehouse_id,
                    'warehouse' => $purchase->warehouse,
                    'user' => $purchase->user,
                    'purchase_date' => $purchase->purchase_date,
                    'reference_no' => $purchase->reference_no,
                    'subtotal' => $purchase->subtotal,
                    'total_discount' => $purchase->total_discount,
                    'total_tax' => $purchase->total_tax,
                    'total_amount' => $purchase->total_amount,
                    'paid_amount' => $purchase->paid_amount,
                    'payment_status' => $purchase->payment_status,
                    'status' => $purchase->status,
                    'notes' => $purchase->notes,
                    'shipping_method' => $purchase->shipping_method,
                    'shipping_cost' => $purchase->shipping_cost,
                    'payment_method' => $purchase->payment_method,
                    'expected_delivery_date' => $purchase->expected_delivery_date,
                    'delivered_date' => $purchase->delivered_date,
                    'created_at' => $purchase->created_at,
                    'updated_at' => $purchase->updated_at,
                    'items' => $purchase->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => (float) $item->quantity,
                            'purchase_price' => (float) $item->purchase_price,
                            'subtotal' => (float) $item->subtotal,
                            'discount_percent' => (float) ($item->discount_percent ?? 0),
                            'discount_amount' => (float) ($item->discount_amount ?? 0),
                            'tax_percent' => (float) ($item->tax_percent ?? 0),
                            'tax_amount' => (float) ($item->tax_amount ?? 0),
                            'total' => (float) $item->total,
                        ];
                    }),
                    'payments' => $purchase->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => (float) $payment->amount,
                            'payment_date' => $payment->payment_date,
                            'payment_method' => $payment->payment_method,
                            'reference_no' => $payment->reference_no,
                            'installment_number' => $payment->installment_number,
                            'created_at' => $payment->created_at,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Purchase show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT/PATCH /api/purchases/{id}
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): PurchaseResource|JsonResponse
    {
        try {
            $this->validatePurchaseModifiable($purchase);

            return DB::transaction(function () use ($request, $purchase) {
                // Only update payment and status fields
                $paidAmount = (float) ($request->paid_amount ?? $purchase->paid_amount);
                $totalAmount = (float) $purchase->total_amount;
                $paymentStatus = $this->determinePaymentStatus($paidAmount, $totalAmount);

                // Update purchase
                $purchase->update([
                    'paid_amount' => $paidAmount,
                    'payment_status' => $paymentStatus,
                    'status' => $request->status ?? $purchase->status,
                    'updated_by' => Auth::id(),
                ]);

                return new PurchaseResource(
                    $purchase->load(['supplier', 'warehouse', 'user', 'items.product', 'payments'])
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

                // Delete payments first
                $purchase->payments()->delete();
                
                // Delete purchase items
                $purchase->items()->delete();
                
                // Delete purchase
                $purchase->delete();

                return response()->json([
                    'message' => 'Purchase deleted successfully.',
                    'purchase_id' => $purchase->id
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

                // Update purchase status and delivered date
                $purchase->update([
                    'status' => 'received',
                    'delivered_date' => now(),
                    'updated_by' => Auth::id(),
                ]);

                // Post to accounting
                $this->postToAccounting($purchase, (float) $purchase->total_amount);

                return response()->json([
                    'message' => 'Purchase received successfully',
                    'purchase' => new PurchaseResource($purchase->load(['supplier', 'warehouse', 'user', 'items.product', 'payments']))
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

                // Get the next installment number
                $lastPayment = $purchase->payments()->latest('installment_number')->first();
                $installmentNumber = $lastPayment ? $lastPayment->installment_number + 1 : 1;

                // Create payment record
                $payment = $purchase->payments()->create([
                    'amount' => $paymentAmount,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'reference_no' => $validated['reference_no'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'installment_number' => $installmentNumber,
                    'created_by' => Auth::id()
                ]);

                // Determine new payment status
                $paymentStatus = $this->determinePaymentStatus($newPaidAmount, $totalAmount);

                // Update purchase with new paid amount and status
                $purchase->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $paymentStatus,
                    'updated_by' => Auth::id(),
                ]);

                // Update accounting entries
                $this->updateAccountingForPayment($purchase, $paymentAmount);

                return response()->json([
                    'message' => 'Payment added successfully',
                    'payment' => $payment,
                    'purchase' => new PurchaseResource($purchase->load(['supplier', 'warehouse', 'payments']))
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
        $inventoryAccountId = config('accounts.inventory', 4);
        $accountsPayableId = config('accounts.accounts_payable', 5);

        // Post to Accounting: Debit Inventory, Credit Accounts Payable
        $this->accountingService->createEntry(
            $purchase->purchase_date,
            "Purchase Invoice #{$purchase->reference_no}",
            [
                ['account_id' => $inventoryAccountId, 'debit' => $totalAmount, 'credit' => 0],
                ['account_id' => $accountsPayableId, 'debit' => 0, 'credit' => $totalAmount],
            ],
            'purchase',
            $purchase->id
        );
    }

    private function updateAccountingForPayment(Purchase $purchase, float $paymentAmount): void
    {
        // Get account IDs from config or use defaults
        $accountsPayableId = config('accounts.accounts_payable', 5);
        $cashAccountId = config('accounts.cash', 1);

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