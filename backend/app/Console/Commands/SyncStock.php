<?php
// app/Console/Commands/SyncStock.php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Console\Command;

class SyncStock extends Command
{
    protected $signature = 'stock:sync {--product=} {--warehouse=}';
    protected $description = 'Sync stock quantities based on transaction history';

    protected $stockService;

    public function __construct(StockService $stockService)
    {
        parent::__construct();
        $this->stockService = $stockService;
    }

    public function handle()
    {
        $productId = $this->option('product');
        $warehouseId = $this->option('warehouse');

        if ($productId && $warehouseId) {
            // Sync specific product in specific warehouse
            $result = $this->stockService->syncProductStock($productId, $warehouseId);
            $this->info("Synced product {$productId} in warehouse {$warehouseId}: " . 
                        ($result['synced'] ? "Updated from {$result['previous_stock']} to {$result['calculated_stock']}" : "No change"));
        } elseif ($productId) {
            // Sync product across all warehouses
            $warehouses = Warehouse::all();
            foreach ($warehouses as $warehouse) {
                $result = $this->stockService->syncProductStock($productId, $warehouse->id);
                $this->line("Warehouse {$warehouse->name}: " . 
                            ($result['synced'] ? "Updated from {$result['previous_stock']} to {$result['calculated_stock']}" : "No change"));
            }
        } else {
            // Sync all products
            $products = Product::all();
            $warehouses = Warehouse::all();
            
            $this->info("Syncing " . $products->count() . " products across " . $warehouses->count() . " warehouses...");
            
            $totalSynced = 0;
            foreach ($products as $product) {
                foreach ($warehouses as $warehouse) {
                    $result = $this->stockService->syncProductStock($product->id, $warehouse->id);
                    if ($result['synced']) {
                        $totalSynced++;
                        $this->line("Synced {$product->name} in {$warehouse->name}: {$result['previous_stock']} -> {$result['calculated_stock']}");
                    }
                }
            }
            
            $this->info("Sync completed. {$totalSynced} stock records were updated.");
        }
        
        return Command::SUCCESS;
    }
}