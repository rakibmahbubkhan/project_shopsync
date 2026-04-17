<?php

namespace App\Services;

use App\Models\StockLog;
use App\Models\InventoryLedger;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Product;
use App\Models\Warehouse;

class StockService
{
    /**
     * Increase stock for a product in a specific warehouse
     */
    public function increaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): void {
        DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $userId
        ) {
            // Get or create product stock record
            $productStock = ProductStock::firstOrNew([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId
            ]);
            
            $balanceBefore = $productStock->quantity ?? 0;
            $newQuantity = $balanceBefore + $quantity;
            
            // Set values for new record
            $productStock->quantity = $newQuantity;
            $productStock->save();

            // Create stock log entry
            StockLog::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'type' => 'in',
                'quantity' => $quantity,
                'old_quantity' => $balanceBefore,
                'new_quantity' => $newQuantity,
                'cost_price' => $unitCost,
                'created_by' => $userId ?? Auth::id(),
                'notes' => "Stock increased from {$referenceType} #{$referenceId}"
            ]);

            // Create ledger entry
            InventoryLedger::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'movement_type'  => 'in',
                'quantity'       => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after'  => $newQuantity,
                'unit_cost'      => $unitCost,
                'total_cost'     => $quantity * $unitCost,
                'user_id'        => $userId ?? Auth::id(),
                'created_at'     => now()
            ]);
        });
    }

    /**
     * Decrease stock for a product in a specific warehouse
     * 
     * @throws ValidationException if insufficient stock
     */
    public function decreaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): void {
        DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $userId
        ) {
            // Get stock record for this product in this warehouse
            $productStock = ProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            // Fix: If record is missing or insufficient stock, provide descriptive error
            $actualStock = $productStock ? $productStock->quantity : 0;
            
            if (!$productStock || $actualStock < $quantity) {
                throw ValidationException::withMessages([
                    'stock' => "Insufficient stock in warehouse. " .
                            "Product ID: {$productId}, Warehouse ID: {$warehouseId}, " .
                            "Requested: {$quantity}, Available: {$actualStock}"
                ]);
            }

            // Store balance before for ledger
            $balanceBefore = $productStock->quantity;
            $newQuantity = $balanceBefore - $quantity;

            // Decrease quantity
            $productStock->decrement('quantity', $quantity);

            // Create stock log entry
            StockLog::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'type' => 'out',
                'quantity' => $quantity,
                'old_quantity' => $balanceBefore,
                'new_quantity' => $newQuantity,
                'cost_price' => $unitCost,
                'created_by' => $userId ?? Auth::id(),
                'notes' => "Stock decreased from {$referenceType} #{$referenceId}"
            ]);

            // Create ledger entry
            InventoryLedger::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'movement_type'  => 'out',
                'quantity'       => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after'  => $newQuantity,
                'unit_cost'      => $unitCost,
                'total_cost'     => $quantity * $unitCost,
                'user_id'        => $userId ?? Auth::id(),
                'created_at'     => now()
            ]);
        });
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        float $unitCost,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): void {
        DB::transaction(function () use (
            $productId,
            $fromWarehouseId,
            $toWarehouseId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $userId
        ) {
            // Decrease from source warehouse
            $this->decreaseStock(
                $productId,
                $fromWarehouseId,
                $quantity,
                $unitCost,
                $referenceType . '_transfer_out',
                $referenceId,
                $userId
            );

            // Increase in destination warehouse
            $this->increaseStock(
                $productId,
                $toWarehouseId,
                $quantity,
                $unitCost,
                $referenceType . '_transfer_in',
                $referenceId,
                $userId
            );
        });
    }

    /**
     * Get stock balance for a product in a specific warehouse
     */
    public function getStockBalance(int $productId, int $warehouseId): float
    {
        $productStock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $productStock ? $productStock->quantity : 0;
    }

    /**
     * Get total stock for a product across all warehouses
     */
    public function getTotalStock(int $productId): float
    {
        return ProductStock::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Get stock movements for a product
     */
    public function getStockMovements(
        int $productId, 
        ?int $warehouseId = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ) {
        $query = InventoryLedger::with(['user', 'warehouse'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query->paginate(15);
    }

    /**
     * Get stock logs for a product
     */
    public function getStockLogs(
        int $productId,
        ?int $warehouseId = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ) {
        $query = StockLog::with(['product', 'user', 'warehouse'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query->paginate(15);
    }

    /**
     * Get the average cost of a product at a specific warehouse.
     */
    public function getAverageCost(int $productId, int $warehouseId): float
    {
        // Get warehouse-specific stock record with average cost
        $productStock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        // Fallback to product's cost price if no warehouse-specific cost exists
        if (!$productStock || $productStock->avg_cost <= 0) {
            $product = Product::find($productId);
            return $product ? $product->cost_price : 0;
        }

        return $productStock->avg_cost;
    }

    /**
     * Process a sale return (full workflow)
     */
    public function processSaleReturn(Sale $sale, int $productId, int $quantity, int $userId): void 
    {
        $item = $sale->items()
            ->where('product_id', $productId)
            ->firstOrFail();

        if ($quantity > $item->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Return quantity exceeds sold quantity.'
            ]);
        }

        DB::transaction(function () use ($sale, $item, $quantity, $userId) {
            $refundAmount = $quantity * $item->selling_price;
            $cogsReversal = $quantity * $item->cost_price;
            $profitReversal = $refundAmount - $cogsReversal;

            // 1️⃣ Restore Stock
            $this->increaseStock(
                $item->product_id,
                $sale->warehouse_id,
                $quantity,
                $item->cost_price,
                'sale_return',
                $sale->id,
                $userId
            );

            // 2️⃣ Create Return Record
            SaleReturn::create([
                'sale_id'        => $sale->id,
                'product_id'     => $item->product_id,
                'quantity'       => $quantity,
                'refund_amount'  => $refundAmount,
                'cost_price'     => $item->cost_price,
                'profit_reversed' => $profitReversal,
                'processed_by'   => $userId,
                'status'         => 'approved'
            ]);

            // 3️⃣ Update Sale Totals
            $sale->decrement('total_amount', $refundAmount);
            $sale->decrement('total_cogs', $cogsReversal);
            $sale->decrement('gross_profit', $profitReversal);
        });
    }

    /**
     * Prepare a return record (pending approval if threshold exceeded).
     */
    public function prepareSaleReturn(
        Sale $sale, 
        int $productId, 
        int $quantity, 
        int $userId, 
        string $reason
    ): SaleReturn {
        $item = $sale->items()
            ->where('product_id', $productId)
            ->firstOrFail();
        
        // Calculate already returned quantity
        $alreadyReturned = SaleReturn::where('sale_id', $sale->id)
            ->where('product_id', $productId)
            ->sum('quantity');

        $remainingQty = $item->quantity - $alreadyReturned;

        if ($quantity > $remainingQty) {
            throw new \Exception("Return quantity exceeds remaining quantity. Available: {$remainingQty}");
        }

        $refundAmount = $quantity * $item->selling_price;

        return SaleReturn::create([
            'sale_id'        => $sale->id,
            'product_id'     => $productId,
            'quantity'       => $quantity,
            'refund_amount'  => $refundAmount,
            'reason'         => $reason,
            'status'         => 'pending',
            'created_by'     => $userId,
        ]);
    }

    /**
     * Finalize stock return (increments stock back).
     */
    public function finalizeSaleReturn(SaleReturn $return): void
    {
        DB::transaction(function () use ($return) {
            $sale = $return->sale;
            $productId = $return->product_id;
            $quantity = $return->quantity;

            $saleItem = $sale->items()
                ->where('product_id', $productId)
                ->firstOrFail();

            // 1️⃣ Restore Stock in the specific warehouse using increaseStock method
            $this->increaseStock(
                $productId,
                $sale->warehouse_id,
                $quantity,
                $saleItem->cost_price,
                'sale_return',
                $return->id,
                $return->created_by
            );

            // 2️⃣ Adjust sale totals
            $cogsReduction = $quantity * $saleItem->cost_price;
            $profitReduction = $return->refund_amount - $cogsReduction;

            $sale->decrement('total_amount', $return->refund_amount);
            $sale->decrement('total_cogs', $cogsReduction);
            $sale->decrement('gross_profit', $profitReduction);

            // 3️⃣ Mark return approved
            $return->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Update average cost for a product in a warehouse
     */
    public function updateAverageCost(int $productId, int $warehouseId, float $newCost, float $quantity): void
    {
        $productStock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($productStock && $productStock->quantity > 0) {
            $currentTotalCost = $productStock->avg_cost * $productStock->quantity;
            $newTotalCost = $currentTotalCost + ($newCost * $quantity);
            $newAvgCost = $newTotalCost / ($productStock->quantity + $quantity);
            
            $productStock->avg_cost = $newAvgCost;
            $productStock->save();
        }
    }
}