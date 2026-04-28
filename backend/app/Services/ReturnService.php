<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReturnService
{
    /**
     * Process a sale return using ONLY DB facade
     * No Eloquent models to avoid infinite recursion
     */
    public function processReturn(array $data): array
    {
        $userId = Auth::id();
        $now = now()->toDateTimeString();
        
        Log::info('ReturnService: Starting return', [
            'sale_id' => $data['sale_id'],
            'items' => count($data['items']),
            'user_id' => $userId
        ]);

        return DB::transaction(function () use ($data, $userId, $now) {
            
            // Step 1: Get and validate sale
            $sale = $this->getSale($data['sale_id']);
            
            // Step 2: Get and validate all sale items
            $saleItems = $this->getSaleItems($data['sale_id']);
            
            // Step 3: Validate return items and calculate totals
            $validation = $this->validateReturnItems($data['items'], $saleItems, $data['sale_id']);
            
            // Step 4: Create return record
            $returnId = $this->createReturnRecord($data, $validation['totalAmount'], $userId, $now);
            
            // Step 5: Create return items
            $this->createReturnItems($returnId, $validation['items'], $now);
            
            // Step 6: Create refund record
            $this->createRefund($returnId, $validation['totalAmount'], $data['refund_method'], $userId, $now);
            
            // Step 7: Restore stock to warehouse
            $this->restoreStockBatch($validation['stockItems'], $sale->warehouse_id, $returnId, $data['sale_id'], $userId, $now);
            
            // Step 8: Update sale totals
            $this->updateSaleTotals($data['sale_id'], $validation['totalAmount'], $sale, $now);
            
            // Step 9: Update product stock quantities
            $this->updateProductStockQuantities($validation['stockItems']);
            
            Log::info('ReturnService: Return completed', [
                'return_id' => $returnId,
                'amount' => $validation['totalAmount']
            ]);

            return [
                'id' => $returnId,
                'sale_id' => (int) $data['sale_id'],
                'total_amount' => $validation['totalAmount'],
                'status' => 'completed',
                'reason' => $data['reason'],
                'return_date' => $now,
                'items' => $validation['itemsSummary'],
                'refund' => [
                    'amount' => $validation['totalAmount'],
                    'payment_method' => $data['refund_method'],
                    'status' => 'completed'
                ]
            ];
        });
    }

    /**
     * Get sale data
     */
    private function getSale(int $saleId): object
    {
        $sale = DB::table('sales')
            ->where('id', $saleId)
            ->first([
                'id', 
                'customer_id', 
                'warehouse_id', 
                'total_amount', 
                'paid_amount', 
                'total_cogs', 
                'gross_profit', 
                'payment_status'
            ]);

        if (!$sale) {
            throw new \Exception('Sale not found.');
        }

        if (!$sale->warehouse_id) {
            throw new \Exception('Sale has no warehouse assigned. Cannot process return.');
        }

        return $sale;
    }

    /**
     * Get sale items for validation
     */
    private function getSaleItems(int $saleId): object
    {
        $items = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sale_items.sale_id', $saleId)
            ->select(
                'sale_items.id',
                'sale_items.product_id',
                'sale_items.quantity',
                'sale_items.selling_price',
                'sale_items.cost_price',
                'sale_items.subtotal',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get();

        if ($items->isEmpty()) {
            throw new \Exception('Sale has no items.');
        }

        return $items;
    }

    /**
     * Validate return items and calculate totals
     */
    private function validateReturnItems(array $returnItems, object $saleItems, int $saleId): array
    {
        $totalAmount = 0;
        $validatedItems = [];
        $stockItems = [];
        $itemsSummary = [];

        // Get already returned quantities
        $alreadyReturned = $this->getAlreadyReturnedQuantities($saleId);

        foreach ($returnItems as $itemData) {
            $saleItem = $saleItems->firstWhere('product_id', $itemData['product_id']);

            if (!$saleItem) {
                $product = DB::table('products')->where('id', $itemData['product_id'])->first();
                throw new \Exception(
                    'Product "' . ($product->name ?? 'Unknown') . '" was not found in this sale.'
                );
            }

            // Calculate available quantity
            $alreadyReturnedQty = $alreadyReturned[$itemData['product_id']] ?? 0;
            $availableQty = $saleItem->quantity - $alreadyReturnedQty;

            if ($availableQty <= 0) {
                throw new \Exception(
                    "Product '{$saleItem->product_name}' has already been fully returned."
                );
            }

            if ($itemData['quantity'] > $availableQty) {
                throw new \Exception(
                    "Return quantity ({$itemData['quantity']}) exceeds available quantity ({$availableQty}) " .
                    "for '{$saleItem->product_name}'."
                );
            }

            $returnQty = $itemData['quantity'];
            $itemRefundAmount = $returnQty * $saleItem->selling_price;
            $totalAmount += $itemRefundAmount;

            // Build validated item for insertion
            $validatedItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $returnQty,
                'selling_price' => $saleItem->selling_price,
                'cost_price' => $saleItem->cost_price,
                'subtotal' => $itemRefundAmount,
                'discount' => $itemData['discount'] ?? 0,
                'tax' => $itemData['tax'] ?? 0,
            ];

            // Stock restoration data
            $stockItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $returnQty,
                'cost_price' => $saleItem->cost_price,
                'product_name' => $saleItem->product_name,
            ];

            // Summary for response
            $itemsSummary[] = [
                'product_id' => $itemData['product_id'],
                'product_name' => $saleItem->product_name,
                'quantity' => $returnQty,
                'price' => (float) $saleItem->selling_price,
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
     * Get already returned quantities for a sale
     */
    private function getAlreadyReturnedQuantities(int $saleId): array
    {
        $returned = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
            ->where('sale_returns.sale_id', $saleId)
            ->whereIn('sale_returns.status', ['approved', 'completed'])
            ->select(
                'sale_return_items.product_id',
                DB::raw('SUM(sale_return_items.quantity) as total_returned')
            )
            ->groupBy('sale_return_items.product_id')
            ->pluck('total_returned', 'product_id')
            ->toArray();

        return $returned;
    }

    /**
     * Create the sale return record
     */
    private function createReturnRecord(array $data, float $totalAmount, int $userId, string $now): int
    {
        return DB::table('sale_returns')->insertGetId([
            'sale_id' => $data['sale_id'],
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
                'sale_return_id' => $returnId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'selling_price' => $item['selling_price'],
                'cost_price' => $item['cost_price'],
                'subtotal' => $item['subtotal'],
                'discount' => $item['discount'],
                'tax' => $item['tax'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('sale_return_items')->insert($insertData);
    }

    /**
     * Create refund record
     */
    private function createRefund(int $returnId, float $amount, string $method, int $userId, string $now): void
    {
        DB::table('refunds')->insert([
            'sale_return_id' => $returnId,
            'amount' => $amount,
            'payment_method' => $method,
            'reference_number' => $this->generateRefundReference($method, $returnId),
            'status' => 'completed',
            'processed_by' => $userId,
            'notes' => 'Refund processed for return #' . $returnId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Generate refund reference number
     */
    private function generateRefundReference(string $method, int $returnId): ?string
    {
        if ($method === 'cash') {
            return null;
        }

        $prefix = match($method) {
            'card' => 'CRD',
            'bank_transfer' => 'BNK',
            'mobile_banking' => 'MBK',
            default => 'REF',
        };

        return $prefix . '-' . date('Ymd') . '-' . str_pad($returnId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Restore stock for all returned items
     */
    private function restoreStockBatch(array $stockItems, int $warehouseId, int $returnId, int $saleId, int $userId, string $now): void
    {
        foreach ($stockItems as $item) {
            $this->restoreStock(
                $item['product_id'],
                $warehouseId,
                $item['quantity'],
                $item['cost_price'],
                $returnId,
                $saleId,
                $item['product_name'],
                $userId,
                $now
            );
        }
    }

    /**
     * Restore stock for a single product
     */
    private function restoreStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        int $returnId,
        int $saleId,
        string $productName,
        int $userId,
        string $now
    ): void {
        // Get current stock
        $existingStock = DB::table('product_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first(['id', 'quantity', 'avg_cost']);

        $balanceBefore = $existingStock ? (float) $existingStock->quantity : 0;
        $newQuantity = $balanceBefore + $quantity;

        // Update or create product_stocks
        if ($existingStock) {
            DB::table('product_stocks')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->update([
                    'quantity' => $newQuantity,
                    'updated_at' => $now
                ]);
        } else {
            DB::table('product_stocks')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $newQuantity,
                'avg_cost' => $unitCost,
                'last_purchase_price' => $unitCost,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // Create stock log entry
        DB::table('stock_logs')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => 'sale_return',
            'reference_id' => $returnId,
            'type' => 'in',
            'quantity' => $quantity,
            'old_quantity' => $balanceBefore,
            'new_quantity' => $newQuantity,
            'cost_price' => $unitCost,
            'created_by' => $userId,
            'notes' => "Return from Sale #{$saleId} - '{$productName}'",
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Create inventory ledger entry
        DB::table('inventory_ledgers')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => 'sale_return',
            'reference_id' => $returnId,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'balance_before' => $balanceBefore,
            'balance_after' => $newQuantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'user_id' => $userId,
            'created_at' => $now
        ]);

        Log::info("ReturnService: Stock restored for product {$productId}", [
            'warehouse' => $warehouseId,
            'quantity' => $quantity,
            'balance_before' => $balanceBefore,
            'balance_after' => $newQuantity
        ]);
    }

    /**
     * Update sale totals after return
     */
    /**
 * Update sale totals after return
 * FIXED: Match exact ENUM values from database
 */
private function updateSaleTotals(int $saleId, float $returnAmount, object $sale, string $now): void
{
    $newTotalAmount = max(0, $sale->total_amount - $returnAmount);
    
    DB::table('sales')
        ->where('id', $saleId)
        ->update([
            'total_amount' => $newTotalAmount,
            'updated_at' => $now,
        ]);

    if ((float) $sale->paid_amount > 0) {
        $newPaidAmount = max(0, (float) $sale->paid_amount - $returnAmount);
        
        if ($newPaidAmount >= $newTotalAmount && $newTotalAmount > 0) {
            $newStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $newStatus = 'partial';
        } else {
            $newStatus = 'unpaid';
        }

        // Use DB::raw for the status to ensure proper quoting
        DB::table('sales')
            ->where('id', $saleId)
            ->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => DB::raw("'{$newStatus}'"),
                'updated_at' => $now,
            ]);
    }
}

    /**
     * Update product stock quantities in products table
     */
    private function updateProductStockQuantities(array $stockItems): void
    {
        foreach ($stockItems as $item) {
            DB::table('products')
                ->where('id', $item['product_id'])
                ->increment('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Get returns list with items and refunds
     */
    public function getReturns(array $filters = [], int $perPage = 15): object
    {
        $query = DB::table('sale_returns')
            ->leftJoin('sales', 'sale_returns.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sale_returns.user_id', '=', 'users.id')
            ->select(
                'sale_returns.id',
                'sale_returns.sale_id',
                'sale_returns.return_date',
                'sale_returns.reason',
                'sale_returns.total_amount',
                'sale_returns.status',
                'sale_returns.notes',
                'sale_returns.created_at',
                'customers.name as customer_name',
                'customers.mobile_number as customer_mobile',
                'users.name as processed_by_name'
            );

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sale_returns.id', 'like', "%{$search}%")
                  ->orWhere('sale_returns.sale_id', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('sale_returns.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('sale_returns.return_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('sale_returns.return_date', '<=', $filters['date_to']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'latest';
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('sale_returns.return_date', 'asc');
                break;
            case 'highest':
                $query->orderBy('sale_returns.total_amount', 'desc');
                break;
            case 'lowest':
                $query->orderBy('sale_returns.total_amount', 'asc');
                break;
            default:
                $query->orderBy('sale_returns.created_at', 'desc');
                break;
        }

        $returns = $query->paginate($perPage);

        // Get items and refunds for each return
        $returnIds = collect($returns->items())->pluck('id')->toArray();
        
        if (!empty($returnIds)) {
            $items = DB::table('sale_return_items')
                ->join('products', 'sale_return_items.product_id', '=', 'products.id')
                ->whereIn('sale_return_items.sale_return_id', $returnIds)
                ->select(
                    'sale_return_items.id',
                    'sale_return_items.sale_return_id',
                    'sale_return_items.product_id',
                    'sale_return_items.quantity',
                    'sale_return_items.selling_price',
                    'sale_return_items.subtotal',
                    'products.name as product_name',
                    'products.sku as product_sku'
                )
                ->get()
                ->groupBy('sale_return_id');

            $refunds = DB::table('refunds')
                ->whereIn('sale_return_id', $returnIds)
                ->select('id', 'sale_return_id', 'amount', 'payment_method', 'status')
                ->get()
                ->groupBy('sale_return_id');

            $returns->getCollection()->transform(function ($return) use ($items, $refunds) {
                $return->items = $items->get($return->id, collect([]))->toArray();
                $return->refund = $refunds->get($return->id, collect([]))->first();
                return $return;
            });
        }

        return $returns;
    }

    /**
     * Get single return details
     */
    public function getReturn(int $returnId): ?object
    {
        $return = DB::table('sale_returns')
            ->leftJoin('sales', 'sale_returns.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'sale_returns.user_id', '=', 'users.id')
            ->leftJoin('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->where('sale_returns.id', $returnId)
            ->select(
                'sale_returns.*',
                'customers.name as customer_name',
                'customers.mobile_number as customer_mobile',
                'users.name as processed_by_name',
                'warehouses.name as warehouse_name'
            )
            ->first();

        if (!$return) {
            return null;
        }

        // Get items
        $return->items = DB::table('sale_return_items')
            ->join('products', 'sale_return_items.product_id', '=', 'products.id')
            ->where('sale_return_items.sale_return_id', $returnId)
            ->select(
                'sale_return_items.*',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get()
            ->toArray();

        // Get refunds
        $return->refunds = DB::table('refunds')
            ->where('sale_return_id', $returnId)
            ->get()
            ->toArray();

        return $return;
    }

    /**
     * Search sales for return processing
     */
    public function searchSalesForReturn(string $search): array
    {
        if (strlen(trim($search)) < 2) {
            return [];
        }

        $sales = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->where(function ($q) use ($search) {
                $q->where('sales.id', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.mobile_number', 'like', "%{$search}%");
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('sale_items')
                  ->whereColumn('sale_items.sale_id', 'sales.id');
            })
            ->select(
                'sales.id',
                'sales.sale_date',
                'sales.total_amount',
                'sales.paid_amount',
                'sales.payment_status',
                'customers.name as customer_name',
                'customers.mobile_number as customer_mobile',
                'warehouses.name as warehouse_name'
            )
            ->latest('sales.sale_date')
            ->limit(20)
            ->get();

        if ($sales->isEmpty()) {
            return [];
        }

        $saleIds = $sales->pluck('id')->toArray();

        // Get items for all sales
        $saleItems = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select(
                'sale_items.id',
                'sale_items.sale_id',
                'sale_items.product_id',
                'sale_items.quantity',
                'sale_items.selling_price',
                'sale_items.cost_price',
                'sale_items.subtotal',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get()
            ->groupBy('sale_id');

        // Get already returned quantities
        $returnedQtys = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
            ->whereIn('sale_returns.sale_id', $saleIds)
            ->whereIn('sale_returns.status', ['approved', 'completed'])
            ->select(
                'sale_returns.sale_id',
                'sale_return_items.product_id',
                DB::raw('SUM(sale_return_items.quantity) as total_returned')
            )
            ->groupBy('sale_returns.sale_id', 'sale_return_items.product_id')
            ->get()
            ->groupBy('sale_id');

        // Build result
        $result = [];
        foreach ($sales as $sale) {
            $items = $saleItems->get($sale->id, collect([]));
            $returned = $returnedQtys->get($sale->id, collect([]))->keyBy('product_id');

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
                        'selling_price' => (float) $item->selling_price,
                        'cost_price' => (float) $item->cost_price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }
            }

            if (!empty($formattedItems)) {
                $result[] = [
                    'id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'total_amount' => (float) $sale->total_amount,
                    'paid_amount' => (float) $sale->paid_amount,
                    'payment_status' => $sale->payment_status,
                    'customer' => [
                        'id' => null,
                        'name' => $sale->customer_name,
                        'mobile_number' => $sale->customer_mobile,
                    ],
                    'warehouse' => [
                        'id' => null,
                        'name' => $sale->warehouse_name,
                    ],
                    'items' => $formattedItems,
                ];
            }
        }

        return $result;
    }

    /**
     * Approve pending return
     */
    public function approveReturn(int $returnId): bool
    {
        $return = DB::table('sale_returns')->where('id', $returnId)->first();

        if (!$return) {
            throw new \Exception('Return not found.');
        }

        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be approved.');
        }

        $updated = DB::table('sale_returns')
            ->where('id', $returnId)
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'updated_at' => now()
            ]);

        return (bool) $updated;
    }

    /**
     * Reject pending return
     */
    public function rejectReturn(int $returnId): bool
    {
        $return = DB::table('sale_returns')->where('id', $returnId)->first();

        if (!$return) {
            throw new \Exception('Return not found.');
        }

        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be rejected.');
        }

        $updated = DB::table('sale_returns')
            ->where('id', $returnId)
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);

        return (bool) $updated;
    }

    /**
     * Get return statistics
     */
    public function getStats(): array
    {
        $stats = DB::table('sale_returns')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                COALESCE(SUM(total_amount), 0) as total_refunded
            ")
            ->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'pending' => (int) ($stats->pending ?? 0),
            'approved' => (int) ($stats->approved ?? 0),
            'completed' => (int) ($stats->completed ?? 0),
            'rejected' => (int) ($stats->rejected ?? 0),
            'totalRefunded' => (float) ($stats->total_refunded ?? 0),
        ];
    }
}