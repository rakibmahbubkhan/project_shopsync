<?php

namespace App\Services;

use App\Models\StockLog;
use App\Models\InventoryLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            // Get or create stock record for this product in this warehouse
            $stock = StockLog::firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId
                ],
                ['quantity' => 0]
            );

            // Increase quantity
            $stock->increment('quantity', $quantity);

            // Create ledger entry
            InventoryLedger::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'movement_type'  => 'in',
                'quantity'       => $quantity,
                'balance_before' => $stock->quantity - $quantity,
                'balance_after'  => $stock->quantity,
                'unit_cost'      => $unitCost,
                'total_cost'     => $quantity * $unitCost,
                'user_id'        => $userId ?? auth()->id(),
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
            $stock = StockLog::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            // Validate stock exists
            if (!$stock) {
                throw ValidationException::withMessages([
                    'stock' => "Product ID {$productId} has no stock in warehouse ID {$warehouseId}"
                ]);
            }

            // Validate sufficient stock
            if ($stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'stock' => "Insufficient stock for Product ID {$productId} in warehouse ID {$warehouseId}. Available: {$stock->quantity}, Requested: {$quantity}"
                ]);
            }

            // Store balance before for ledger
            $balanceBefore = $stock->quantity;

            // Decrease quantity
            $stock->decrement('quantity', $quantity);

            // Create ledger entry
            InventoryLedger::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'movement_type'  => 'out',
                'quantity'       => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after'  => $stock->quantity,
                'unit_cost'      => $unitCost,
                'total_cost'     => $quantity * $unitCost,
                'user_id'        => $userId ?? auth()->id(),
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
        $stock = StockLog::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * Get total stock for a product across all warehouses
     */
    public function getTotalStock(int $productId): float
    {
        return StockLog::where('product_id', $productId)
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
}