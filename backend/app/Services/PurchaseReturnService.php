<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseReturnService
{
    /**
     * Process a purchase return using ONLY DB facade
     */
    public function processReturn(array $data): array
    {
        $userId = Auth::id();
        $now = now()->toDateTimeString();
        
        Log::info('PurchaseReturnService: Starting return', [
            'purchase_id' => $data['purchase_id'],
            'items' => count($data['items']),
            'user_id' => $userId
        ]);

        return DB::transaction(function () use ($data, $userId, $now) {
            
            // Step 1: Get purchase
            $purchase = $this->getPurchase($data['purchase_id']);
            
            // Step 2: Get purchase items
            $purchaseItems = $this->getPurchaseItems($data['purchase_id']);
            
            // Step 3: Validate and calculate
            $validation = $this->validateReturnItems($data['items'], $purchaseItems, $data['purchase_id']);
            
            // Step 4: Create return record
            $returnId = $this->createReturnRecord($data, $validation['totalAmount'], $purchase, $userId, $now);
            
            // Step 5: Create return items
            $this->createReturnItems($returnId, $validation['items'], $now);
            
            // Step 6: Create supplier credit
            $this->createSupplierCredit($returnId, $purchase->supplier_id, $validation['totalAmount'], $userId, $now);
            
            // Step 7: Decrease stock
            $this->decreaseStockBatch($validation['stockItems'], $purchase->warehouse_id, $returnId, $data['purchase_id'], $userId, $now);
            
            // Step 8: Update purchase totals
            $this->updatePurchaseTotals($data['purchase_id'], $validation['totalAmount'], $purchase, $now);
            
            // Step 9: Update product quantities
            $this->updateProductStockQuantities($validation['stockItems']);
            
            Log::info('PurchaseReturnService: Return completed', [
                'return_id' => $returnId,
                'amount' => $validation['totalAmount']
            ]);

            return [
                'id' => $returnId,
                'purchase_id' => (int) $data['purchase_id'],
                'total_amount' => $validation['totalAmount'],
                'status' => 'completed',
                'reason' => $data['reason'],
                'return_date' => $now,
                'items' => $validation['itemsSummary'],
                'supplier_credit' => [
                    'amount' => $validation['totalAmount'],
                    'status' => 'pending'
                ]
            ];
        });
    }

    /**
     * Get purchase data
     */
    private function getPurchase(int $purchaseId): object
    {
        $purchase = DB::table('purchases')
            ->where('id', $purchaseId)
            ->first([
                'id', 
                'supplier_id', 
                'warehouse_id', 
                'total_amount', 
                'paid_amount', 
                'payment_status',
                'status'
            ]);

        if (!$purchase) {
            throw new \Exception('Purchase not found.');
        }

        if (!$purchase->warehouse_id) {
            throw new \Exception('Purchase has no warehouse assigned.');
        }

        if ($purchase->status !== 'received') {
            throw new \Exception('Only received purchases can be returned. Current status: ' . $purchase->status);
        }

        return $purchase;
    }

    /**
     * Get purchase items - FIXED COLUMN NAMES
     */
    private function getPurchaseItems(int $purchaseId): object
    {
        $items = DB::table('purchase_items')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->where('purchase_items.purchase_id', $purchaseId)
            ->select(
                'purchase_items.id',
                'purchase_items.product_id',
                'purchase_items.quantity',
                // Use purchase_price only
                DB::raw('purchase_items.purchase_price as unit_price'),
                // Use total column
                DB::raw('purchase_items.total as line_total'),
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get();

        if ($items->isEmpty()) {
            throw new \Exception('Purchase has no items.');
        }

        return $items;
    }

    /**
     * Validate return items
     */
    private function validateReturnItems(array $returnItems, object $purchaseItems, int $purchaseId): array
    {
        $totalAmount = 0;
        $validatedItems = [];
        $stockItems = [];
        $itemsSummary = [];

        $alreadyReturned = $this->getAlreadyReturnedQuantities($purchaseId);

        foreach ($returnItems as $itemData) {
            $purchaseItem = $purchaseItems->firstWhere('product_id', $itemData['product_id']);

            if (!$purchaseItem) {
                $product = DB::table('products')->where('id', $itemData['product_id'])->first();
                throw new \Exception(
                    'Product "' . ($product->name ?? 'Unknown') . '" was not found in this purchase.'
                );
            }

            $alreadyReturnedQty = $alreadyReturned[$itemData['product_id']] ?? 0;
            $availableQty = $purchaseItem->quantity - $alreadyReturnedQty;

            if ($availableQty <= 0) {
                throw new \Exception(
                    "Product '{$purchaseItem->product_name}' has already been fully returned."
                );
            }

            if ($itemData['quantity'] > $availableQty) {
                throw new \Exception(
                    "Return quantity ({$itemData['quantity']}) exceeds available ({$availableQty}) " .
                    "for '{$purchaseItem->product_name}'."
                );
            }

            $returnQty = $itemData['quantity'];
            $itemRefundAmount = $returnQty * $purchaseItem->unit_price;
            $totalAmount += $itemRefundAmount;

            $validatedItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $returnQty,
                'purchase_price' => $purchaseItem->unit_price,
                'subtotal' => $itemRefundAmount,
                'discount' => 0,
                'tax' => 0,
            ];

            $stockItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $returnQty,
                'cost_price' => $purchaseItem->unit_price,
                'product_name' => $purchaseItem->product_name,
            ];

            $itemsSummary[] = [
                'product_id' => $itemData['product_id'],
                'product_name' => $purchaseItem->product_name,
                'quantity' => $returnQty,
                'price' => (float) $purchaseItem->unit_price,
                'subtotal' => $itemRefundAmount,
            ];
        }

        if ($totalAmount <= 0) {
            throw new \Exception('Return total amount must be greater than zero.');
        }

        return [
            'totalAmount' => $totalAmount,
            'items' => $validatedItems,
            'stockItems' => $stockItems,
            'itemsSummary' => $itemsSummary,
        ];
    }

    /**
     * Get already returned quantities
     */
    private function getAlreadyReturnedQuantities(int $purchaseId): array
    {
        return DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->where('purchase_returns.purchase_id', $purchaseId)
            ->whereIn('purchase_returns.status', ['approved', 'completed'])
            ->select(
                'purchase_return_items.product_id',
                DB::raw('SUM(purchase_return_items.quantity) as total_returned')
            )
            ->groupBy('purchase_return_items.product_id')
            ->pluck('total_returned', 'product_id')
            ->toArray();
    }

    /**
     * Create return record
     */
    private function createReturnRecord(array $data, float $totalAmount, object $purchase, int $userId, string $now): int
    {
        return DB::table('purchase_returns')->insertGetId([
            'purchase_id' => $data['purchase_id'],
            'supplier_id' => $purchase->supplier_id,
            'warehouse_id' => $purchase->warehouse_id,
            'user_id' => $userId,
            'return_date' => $now,
            'reason' => $data['reason'],
            'total_amount' => $totalAmount,
            'status' => 'completed',
            'notes' => $data['notes'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Create return items
     */
    private function createReturnItems(int $returnId, array $items, string $now): void
    {
        $insertData = [];
        foreach ($items as $item) {
            $insertData[] = [
                'purchase_return_id' => $returnId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'subtotal' => $item['subtotal'],
                'discount' => $item['discount'],
                'tax' => $item['tax'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('purchase_return_items')->insert($insertData);
    }

    /**
     * Create supplier credit
     */
    private function createSupplierCredit(int $returnId, int $supplierId, float $amount, int $userId, string $now): void
    {
        DB::table('supplier_credits')->insert([
            'purchase_return_id' => $returnId,
            'supplier_id' => $supplierId,
            'amount' => $amount,
            'status' => 'pending',
            'notes' => 'Credit from purchase return #' . $returnId,
            'processed_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Decrease stock batch
     */
    private function decreaseStockBatch(array $stockItems, int $warehouseId, int $returnId, int $purchaseId, int $userId, string $now): void
    {
        foreach ($stockItems as $item) {
            $this->decreaseStock(
                $item['product_id'],
                $warehouseId,
                $item['quantity'],
                $item['cost_price'],
                $returnId,
                $purchaseId,
                $item['product_name'],
                $userId,
                $now
            );
        }
    }

    /**
     * Decrease stock for a single product
     */
    private function decreaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        int $returnId,
        int $purchaseId,
        string $productName,
        int $userId,
        string $now
    ): void {
        $existingStock = DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first(['id', 'quantity']);

        $balanceBefore = $existingStock ? (float) $existingStock->quantity : 0;
        $newQuantity = max(0, $balanceBefore - $quantity);

        if ($existingStock) {
            DB::table('product_stocks')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->update([
                    'quantity' => $newQuantity,
                    'updated_at' => $now
                ]);
        }

        // Stock log
        DB::table('stock_logs')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => 'purchase_return',
            'reference_id' => $returnId,
            'type' => 'out',
            'quantity' => $quantity,
            'old_quantity' => $balanceBefore,
            'new_quantity' => $newQuantity,
            'cost_price' => $unitCost,
            'created_by' => $userId,
            'notes' => "Return to supplier - Purchase #{$purchaseId} - '{$productName}'",
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Inventory ledger
        try {
            DB::table('inventory_ledgers')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => 'purchase_return',
                'reference_id' => $returnId,
                'movement_type' => 'out',
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $newQuantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'user_id' => $userId,
                'created_at' => $now
            ]);
        } catch (\Exception $e) {
            // inventory_ledgers table might not exist - that's ok
            Log::warning('Could not insert into inventory_ledgers: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase totals
     */
    private function updatePurchaseTotals(int $purchaseId, float $returnAmount, object $purchase, string $now): void
    {
        $newTotalAmount = max(0, $purchase->total_amount - $returnAmount);
        
        DB::table('purchases')
            ->where('id', $purchaseId)
            ->update([
                'total_amount' => $newTotalAmount,
                'updated_at' => $now,
            ]);

        if ((float) $purchase->paid_amount > 0) {
            $newPaidAmount = max(0, (float) $purchase->paid_amount - $returnAmount);
            
            if ($newPaidAmount >= $newTotalAmount && $newTotalAmount > 0) {
                $newStatus = 'paid';
            } elseif ($newPaidAmount > 0) {
                $newStatus = 'partial';
            } else {
                $newStatus = 'unpaid';
            }

            DB::table('purchases')
                ->where('id', $purchaseId)
                ->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => DB::raw("'{$newStatus}'"),
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Update product stock quantities
     */
    private function updateProductStockQuantities(array $stockItems): void
    {
        foreach ($stockItems as $item) {
            DB::table('products')
                ->where('id', $item['product_id'])
                ->decrement('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Get returns list
     */
    public function getReturns(array $filters = [], int $perPage = 15): object
    {
        $query = DB::table('purchase_returns')
            ->leftJoin('suppliers', 'purchase_returns.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'purchase_returns.user_id', '=', 'users.id')
            ->select(
                'purchase_returns.*',
                'suppliers.name as supplier_name',
                'users.name as processed_by_name'
            );

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('purchase_returns.id', 'like', "%{$search}%")
                  ->orWhere('purchase_returns.purchase_id', 'like', "%{$search}%")
                  ->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('purchase_returns.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('purchase_returns.return_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('purchase_returns.return_date', '<=', $filters['date_to']);
        }

        switch ($filters['sort_by'] ?? 'latest') {
            case 'oldest':
                $query->orderBy('purchase_returns.return_date', 'asc');
                break;
            case 'highest':
                $query->orderBy('purchase_returns.total_amount', 'desc');
                break;
            case 'lowest':
                $query->orderBy('purchase_returns.total_amount', 'asc');
                break;
            default:
                $query->orderBy('purchase_returns.created_at', 'desc');
                break;
        }

        $returns = $query->paginate($perPage);

        $returnIds = collect($returns->items())->pluck('id')->toArray();
        
        if (!empty($returnIds)) {
            $items = DB::table('purchase_return_items')
                ->join('products', 'purchase_return_items.product_id', '=', 'products.id')
                ->whereIn('purchase_return_items.purchase_return_id', $returnIds)
                ->select(
                    'purchase_return_items.id',
                    'purchase_return_items.purchase_return_id',
                    'purchase_return_items.product_id',
                    'purchase_return_items.quantity',
                    'purchase_return_items.purchase_price',
                    'purchase_return_items.subtotal',
                    'products.name as product_name',
                    'products.sku as product_sku'
                )
                ->get()
                ->groupBy('purchase_return_id');

            $credits = DB::table('supplier_credits')
                ->whereIn('purchase_return_id', $returnIds)
                ->select('id', 'purchase_return_id', 'amount', 'status')
                ->get()
                ->groupBy('purchase_return_id');

            $returns->getCollection()->transform(function ($return) use ($items, $credits) {
                $return->items = ($items->get($return->id) ?? collect([]))->toArray();
                $return->supplier_credit = ($credits->get($return->id) ?? collect([]))->first();
                return $return;
            });
        }

        return $returns;
    }

    /**
     * Get single return
     */
    public function getReturn(int $returnId): ?object
    {
        $return = DB::table('purchase_returns')
            ->leftJoin('suppliers', 'purchase_returns.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'purchase_returns.user_id', '=', 'users.id')
            ->where('purchase_returns.id', $returnId)
            ->select(
                'purchase_returns.*',
                'suppliers.name as supplier_name',
                'suppliers.email as supplier_email',
                'suppliers.phone as supplier_phone',
                'users.name as processed_by_name'
            )
            ->first();

        if (!$return) {
            return null;
        }

        $return->items = DB::table('purchase_return_items')
            ->join('products', 'purchase_return_items.product_id', '=', 'products.id')
            ->where('purchase_return_items.purchase_return_id', $returnId)
            ->select(
                'purchase_return_items.*',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get()
            ->toArray();

        $return->supplier_credits = DB::table('supplier_credits')
            ->where('purchase_return_id', $returnId)
            ->get()
            ->toArray();

        return $return;
    }

    /**
     * Search purchases for return
     */
    public function searchPurchasesForReturn(string $search): array
    {
        if (strlen(trim($search)) < 2) {
            return [];
        }

        $purchases = DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->where(function ($q) use ($search) {
                $q->where('purchases.id', 'like', "%{$search}%")
                  ->orWhere('purchases.reference_no', 'like', "%{$search}%")
                  ->orWhere('suppliers.name', 'like', "%{$search}%");
            })
            ->where('purchases.status', 'received')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('purchase_items')
                  ->whereColumn('purchase_items.purchase_id', 'purchases.id');
            })
            ->select(
                'purchases.id',
                'purchases.reference_no',
                'purchases.purchase_date',
                'purchases.total_amount',
                'purchases.paid_amount',
                'purchases.payment_status',
                'purchases.status',
                'suppliers.name as supplier_name',
                'suppliers.phone as supplier_phone',
                'warehouses.name as warehouse_name'
            )
            ->latest('purchases.purchase_date')
            ->limit(20)
            ->get();

        if ($purchases->isEmpty()) {
            return [];
        }

        $purchaseIds = $purchases->pluck('id')->toArray();

        // Get items - FIXED column names
        $purchaseItems = DB::table('purchase_items')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->whereIn('purchase_items.purchase_id', $purchaseIds)
            ->select(
                'purchase_items.id',
                'purchase_items.purchase_id',
                'purchase_items.product_id',
                'purchase_items.quantity',
                'purchase_items.purchase_price',
                DB::raw('COALESCE(purchase_items.total, 0) as subtotal'),
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get()
            ->groupBy('purchase_id');

        // Get already returned
        $returnedQtys = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->whereIn('purchase_returns.purchase_id', $purchaseIds)
            ->whereIn('purchase_returns.status', ['approved', 'completed'])
            ->select(
                'purchase_returns.purchase_id',
                'purchase_return_items.product_id',
                DB::raw('SUM(purchase_return_items.quantity) as total_returned')
            )
            ->groupBy('purchase_returns.purchase_id', 'purchase_return_items.product_id')
            ->get()
            ->groupBy('purchase_id');

        $result = [];
        foreach ($purchases as $purchase) {
            $items = $purchaseItems->get($purchase->id, collect([]));
            $returned = $returnedQtys->get($purchase->id, collect([]))->keyBy('product_id');

            $formattedItems = [];
            foreach ($items as $item) {
                $alreadyReturned = isset($returned[$item->product_id]) 
                    ? (int) $returned[$item->product_id]->total_returned 
                    : 0;
                $available = $item->quantity - $alreadyReturned;

                if ($available > 0) {
                    $formattedItems[] = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'quantity' => (int) $item->quantity,
                        'already_returned' => $alreadyReturned,
                        'available_for_return' => $available,
                        'purchase_price' => (float) $item->purchase_price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }
            }

            if (!empty($formattedItems)) {
                $result[] = [
                    'id' => $purchase->id,
                    'reference_no' => $purchase->reference_no ?: 'PO-' . $purchase->id,
                    'purchase_date' => $purchase->purchase_date,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'payment_status' => $purchase->payment_status,
                    'status' => $purchase->status,
                    'supplier' => [
                        'name' => $purchase->supplier_name,
                        'phone' => $purchase->supplier_phone,
                    ],
                    'warehouse' => [
                        'name' => $purchase->warehouse_name,
                    ],
                    'items' => $formattedItems,
                ];
            }
        }

        return $result;
    }

    /**
     * Approve return
     */
    public function approveReturn(int $returnId): bool
    {
        $return = DB::table('purchase_returns')->where('id', $returnId)->first();

        if (!$return) {
            throw new \Exception('Return not found.');
        }

        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be approved.');
        }

        return (bool) DB::table('purchase_returns')
            ->where('id', $returnId)
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Reject return
     */
    public function rejectReturn(int $returnId): bool
    {
        $return = DB::table('purchase_returns')->where('id', $returnId)->first();

        if (!$return) {
            throw new \Exception('Return not found.');
        }

        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be rejected.');
        }

        return (bool) DB::table('purchase_returns')
            ->where('id', $returnId)
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        try {
            $stats = DB::table('purchase_returns')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    COALESCE(SUM(total_amount), 0) as total_credited
                ")
                ->first();

            return [
                'total' => (int) ($stats->total ?? 0),
                'pending' => (int) ($stats->pending ?? 0),
                'approved' => (int) ($stats->approved ?? 0),
                'completed' => (int) ($stats->completed ?? 0),
                'rejected' => (int) ($stats->rejected ?? 0),
                'totalCredited' => (float) ($stats->total_credited ?? 0),
            ];
        } catch (\Exception $e) {
            Log::error('getStats failed', ['error' => $e->getMessage()]);
            return [
                'total' => 0, 'pending' => 0, 'approved' => 0,
                'completed' => 0, 'rejected' => 0, 'totalCredited' => 0
            ];
        }
    }
}