<?php
// app/Services/StockService.php

namespace App\Services;

use App\Models\StockLog;
use App\Models\InventoryLedger;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;  // Add this import
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;  // Add this import
use App\Models\SaleItem;

class StockService
{
    /**
     * Get current stock for a product in a specific warehouse
     */
    public function getCurrentStock(int $productId, int $warehouseId): float
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        return $stock ? (float) $stock->quantity : 0;
    }

    /**
     * Get average cost for a product in a specific warehouse
     */
    public function getAverageCost(int $productId, int $warehouseId): float
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        if ($stock && $stock->avg_cost > 0) {
            return (float) $stock->avg_cost;
        }
        
        // Fallback to product's cost price
        $product = Product::find($productId);
        return $product ? (float) $product->cost_price : 0;
    }

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
        ?int $userId = null,
        ?string $notes = null
    ): void {
        DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $userId,
            $notes
        ) {
            $userId = $userId ?? Auth::id();
            
            // Get or create product stock record
            $productStock = ProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();
            
            $balanceBefore = $productStock ? (float) $productStock->quantity : 0;
            $newQuantity = $balanceBefore + $quantity;
            
            // Calculate new average cost
            $newAvgCost = $this->calculateNewAverageCost(
                $productStock,
                $quantity,
                $unitCost
            );
            
            // Update or create stock record
            if ($productStock) {
                $productStock->update([
                    'quantity' => $newQuantity,
                    'avg_cost' => $newAvgCost,
                    'last_updated_by' => $userId
                ]);
            } else {
                $productStock = ProductStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $newQuantity,
                    'avg_cost' => $newAvgCost,
                    'last_updated_by' => $userId
                ]);
            }
            
            // Also update the product's total stock quantity (for backward compatibility)
            $this->updateProductTotalStock($productId);
            
            // Create Stock Log entry
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
                'created_by' => $userId,
                'notes' => $notes ?? "Stock increased from {$referenceType} #{$referenceId}"
            ]);
            
            // Create Inventory Ledger entry
            InventoryLedger::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'movement_type' => 'in',
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $newQuantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'user_id' => $userId
            ]);
        });
    }

    /**
     * Decrease stock for a product in a specific warehouse
     */
    public function decreaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $unitCost,
        string $referenceType,
        int $referenceId,
        ?int $userId = null,
        ?string $notes = null
    ): void {
        DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $userId,
            $notes
        ) {
            $userId = $userId ?? Auth::id();
            
            // Get stock record with lock for update
            $productStock = ProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();
            
            $balanceBefore = $productStock ? (float) $productStock->quantity : 0;
            
            // Check sufficient stock
            if ($balanceBefore < $quantity) {
                $product = Product::find($productId);
                throw new \Exception(
                    "Insufficient stock for {$product->name}. " .
                    "Available: {$balanceBefore}, Requested: {$quantity}"
                );
            }
            
            $newQuantity = $balanceBefore - $quantity;
            
            // Update stock record
            $productStock->update([
                'quantity' => $newQuantity,
                'last_updated_by' => $userId
            ]);
            
            // Update product total stock
            $this->updateProductTotalStock($productId);
            
            // Create Stock Log entry
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
                'created_by' => $userId,
                'notes' => $notes ?? "Stock decreased from {$referenceType} #{$referenceId}"
            ]);
            
            // Create Inventory Ledger entry
            InventoryLedger::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'movement_type' => 'out',
                'quantity' => $quantity,
                'balance_before' => $balanceBefore,
                'balance_after' => $newQuantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'user_id' => $userId
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
                $userId,
                "Transfer out to warehouse #{$toWarehouseId}"
            );
            
            // Increase in destination warehouse
            $this->increaseStock(
                $productId,
                $toWarehouseId,
                $quantity,
                $unitCost,
                $referenceType . '_transfer_in',
                $referenceId,
                $userId,
                "Transfer in from warehouse #{$fromWarehouseId}"
            );
        });
    }

    /**
     * Update product's total stock quantity across all warehouses
     */
    private function updateProductTotalStock(int $productId): void
    {
        $totalStock = ProductStock::where('product_id', $productId)->sum('quantity');
        Product::where('id', $productId)->update(['stock_quantity' => $totalStock]);
    }

    /**
     * Calculate new average cost
     */
    private function calculateNewAverageCost(?ProductStock $currentStock, float $newQuantity, float $newUnitCost): float
    {
        if (!$currentStock || $currentStock->quantity <= 0) {
            return $newUnitCost;
        }
        
        $currentTotalCost = $currentStock->quantity * $currentStock->avg_cost;
        $newTotalCost = $currentTotalCost + ($newQuantity * $newUnitCost);
        $totalQuantity = $currentStock->quantity + $newQuantity;
        
        return $totalQuantity > 0 ? $newTotalCost / $totalQuantity : $newUnitCost;
    }

    /**
     * Get total stock for a product across all warehouses
     */
    public function getTotalStock(int $productId): float
    {
        return ProductStock::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Get stock movements for a product
     */
    public function getStockMovements(
        int $productId,
        ?int $warehouseId = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $perPage = 15
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
        
        return $query->paginate($perPage);
    }

    /**
     * Get stock logs for a product
     */
    public function getStockLogs(
        int $productId,
        ?int $warehouseId = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        int $perPage = 15
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
        
        return $query->paginate($perPage);
    }

    /**
     * Process a purchase (increase stock)
     */
    public function processPurchase(Purchase $purchase, int $userId): void
    {
        DB::transaction(function () use ($purchase, $userId) {
            foreach ($purchase->items as $item) {
                $this->increaseStock(
                    $item->product_id,
                    $purchase->warehouse_id,
                    (float) $item->quantity,
                    (float) $item->purchase_price,
                    'purchase',
                    $purchase->id,
                    $userId,
                    "Purchase #{$purchase->reference_no}"
                );
            }
            
            $purchase->update([
                'status' => 'received',
                'delivered_date' => now(),
                'updated_by' => $userId
            ]);
        });
    }

    /**
     * Process a sale (decrease stock)
     */
    public function processSale(Sale $sale, array $items, int $userId): void
    {
        DB::transaction(function () use ($sale, $items, $userId) {
            foreach ($items as $item) {
                $costPrice = $this->getAverageCost($item['product_id'], $sale->warehouse_id);
                
                $this->decreaseStock(
                    $item['product_id'],
                    $sale->warehouse_id,
                    (float) $item['quantity'],
                    $costPrice,
                    'sale',
                    $sale->id,
                    $userId,
                    "Sale #{$sale->id}"
                );
            }
        });
    }

    /**
     * Process a sale return (restore stock)
     */
    public function processSaleReturn(SaleReturn $saleReturn, int $userId): void
    {
        DB::transaction(function () use ($saleReturn, $userId) {
            foreach ($saleReturn->items as $item) {
                $this->increaseStock(
                    $item->product_id,
                    $saleReturn->sale->warehouse_id,
                    (float) $item->quantity,
                    (float) $item->cost_price,
                    'sale_return',
                    $saleReturn->id,
                    $userId,
                    "Return #{$saleReturn->id} for Sale #{$saleReturn->sale_id}"
                );
            }
        });
    }

    /**
     * Sync stock for a product (recalculate from purchase and sale history)
     * Use this to fix stock discrepancies
     */
    public function syncProductStock(int $productId, int $warehouseId): array
    {
        return DB::transaction(function () use ($productId, $warehouseId) {
            // Calculate stock from purchase items
            $purchased = PurchaseItem::where('product_id', $productId)
                ->whereHas('purchase', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                      ->where('status', 'received');
                })
                ->sum('quantity');
            
            // Calculate stock from sale items
            $sold = SaleItem::where('product_id', $productId)
                ->whereHas('sale', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                })
                ->sum('quantity');
            
            // Calculate stock from purchase returns
            $returnedToSupplier = PurchaseReturnItem::where('product_id', $productId)
                ->whereHas('purchaseReturn', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                      ->where('status', 'approved');
                })
                ->sum('quantity');
            
            // Calculate stock from sale returns
            $returnedFromCustomer = SaleReturnItem::where('product_id', $productId)
                ->whereHas('saleReturn', function($q) use ($warehouseId) {
                    $q->whereHas('sale', function($sq) use ($warehouseId) {
                        $sq->where('warehouse_id', $warehouseId);
                    })->where('status', 'approved');
                })
                ->sum('quantity');
            
            $calculatedStock = $purchased - $sold - $returnedToSupplier + $returnedFromCustomer;
            
            // Ensure stock is not negative
            $calculatedStock = max(0, $calculatedStock);
            
            // Get current stock
            $currentStock = $this->getCurrentStock($productId, $warehouseId);
            
            // Update if different
            if ($calculatedStock != $currentStock) {
                $productStock = ProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                
                if ($productStock) {
                    $productStock->update(['quantity' => $calculatedStock]);
                } else {
                    ProductStock::create([
                        'product_id' => $productId,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $calculatedStock,
                        'avg_cost' => 0,
                        'last_updated_by' => Auth::id()
                    ]);
                }
                
                $this->updateProductTotalStock($productId);
            }
            
            return [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'previous_stock' => $currentStock,
                'calculated_stock' => $calculatedStock,
                'synced' => $calculatedStock != $currentStock
            ];
        });
    }

    /**
     * Sync all stock for a product across all warehouses
     */
    public function syncAllProductStock(int $productId): array
    {
        $warehouses = Warehouse::all();
        $results = [];
        
        foreach ($warehouses as $warehouse) {
            $results[] = $this->syncProductStock($productId, $warehouse->id);
        }
        
        return $results;
    }

    /**
     * Get stock summary report
     */
    public function getStockSummary(?int $warehouseId = null): array
    {
        $query = ProductStock::with(['product', 'warehouse']);
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        $stocks = $query->get();
        
        return [
            'total_products_with_stock' => $stocks->count(),
            'total_quantity' => $stocks->sum('quantity'),
            'total_value' => $stocks->sum(function ($stock) {
                return $stock->quantity * $stock->avg_cost;
            }),
            'low_stock_items' => $stocks->filter(function ($stock) {
                return $stock->product && $stock->quantity <= $stock->product->alert_quantity;
            })->count(),
            'out_of_stock_items' => $stocks->filter(function ($stock) {
                return $stock->quantity <= 0;
            })->count(),
            'by_warehouse' => $stocks->groupBy('warehouse_id')->map(function ($items, $warehouseId) {
                $warehouse = Warehouse::find($warehouseId);
                return [
                    'warehouse_name' => $warehouse ? $warehouse->name : 'Unknown',
                    'total_quantity' => $items->sum('quantity'),
                    'total_value' => $items->sum(function ($item) {
                        return $item->quantity * $item->avg_cost;
                    })
                ];
            })
        ];
    }

    /**
     * Get stock valuation report
     */
    public function getStockValuation(): array
    {
        $stocks = ProductStock::with(['product', 'warehouse'])->get();
        
        return [
            'total_value' => $stocks->sum(function($stock) {
                return $stock->quantity * $stock->avg_cost;
            }),
            'total_quantity' => $stocks->sum('quantity'),
            'by_warehouse' => $stocks->groupBy('warehouse_id')->map(function($items, $warehouseId) {
                $warehouse = Warehouse::find($warehouseId);
                return [
                    'warehouse_name' => $warehouse ? $warehouse->name : 'Unknown',
                    'quantity' => $items->sum('quantity'),
                    'value' => $items->sum(function($item) {
                        return $item->quantity * $item->avg_cost;
                    })
                ];
            }),
            'by_product' => $stocks->groupBy('product_id')->map(function($items, $productId) {
                $product = Product::find($productId);
                return [
                    'product_name' => $product ? $product->name : 'Unknown',
                    'sku' => $product ? $product->sku : 'N/A',
                    'quantity' => $items->sum('quantity'),
                    'value' => $items->sum(function($item) {
                        return $item->quantity * $item->avg_cost;
                    })
                ];
            })->sortByDesc('value')->take(10)
        ];
    }
}