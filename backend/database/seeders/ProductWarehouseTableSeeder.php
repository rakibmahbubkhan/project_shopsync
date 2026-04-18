<?php
// database/seeders/ProductWarehouseTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Warehouse;

class ProductWarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $warehouses = Warehouse::all();

        if ($products->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('No products or warehouses found. Please seed products and warehouses first.');
            return;
        }

        $this->command->info('Seeding product_warehouse relationships...');
        
        $relationships = [];
        $totalProducts = $products->count();
        $processedCount = 0;

        foreach ($products as $product) {
            // Determine how many warehouses this product is stored in
            // High-value products might be in more warehouses
            $isHighValue = ($product->selling_price ?? 0) > 500;
            $maxWarehouses = $isHighValue ? 3 : 2;
            $numberOfWarehouses = rand(1, min($maxWarehouses, $warehouses->count()));
            
            $selectedWarehouses = $warehouses->random($numberOfWarehouses);
            
            foreach ($selectedWarehouses as $warehouse) {
                // Generate realistic stock quantities based on product type
                $baseQuantity = $this->getBaseQuantity($product);
                $quantity = rand($baseQuantity['min'], $baseQuantity['max']);
                
                // Calculate average cost (could be same as product cost or slightly varied)
                $avgCost = $product->cost_price ?? rand(50, 1000);
                
                // Add some variation to cost for different warehouses
                $costVariation = rand(-5, 5);
                $finalCost = $avgCost + ($avgCost * $costVariation / 100);
                
                $relationships[] = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                    'avg_cost' => round($finalCost, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $processedCount++;
            
            // Show progress every 10 products
            if ($processedCount % 10 === 0) {
                $this->command->info("Processed {$processedCount}/{$totalProducts} products...");
            }
        }
        
        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($relationships, 100);
        foreach ($chunks as $chunk) {
            DB::table('product_warehouse')->insertOrIgnore($chunk);
        }
        
        $this->command->info('Product warehouse relationships seeded successfully!');
        
        // Display summary statistics
        $totalRelations = DB::table('product_warehouse')->count();
        $totalQuantity = DB::table('product_warehouse')->sum('quantity');
        $totalValue = DB::table('product_warehouse')->sum(DB::raw('quantity * avg_cost'));
        
        $this->command->info("Total relationships: {$totalRelations}");
        $this->command->info("Total stock quantity: " . number_format($totalQuantity) . " units");
        $this->command->info("Total stock value: ৳" . number_format($totalValue, 2));
    }
    
    /**
     * Get base quantity range based on product type or category
     */
    private function getBaseQuantity($product): array
    {
        // Default range
        $min = 10;
        $max = 200;
        
        // You can customize based on product category or type
        if (isset($product->category_id)) {
            switch ($product->category_id) {
                case 1: // Electronics
                    $min = 5;
                    $max = 50;
                    break;
                case 2: // Furniture
                    $min = 2;
                    $max = 20;
                    break;
                case 3: // Clothing
                    $min = 20;
                    $max = 300;
                    break;
                case 4: // Food
                    $min = 50;
                    $max = 500;
                    break;
                default:
                    $min = 10;
                    $max = 100;
            }
        }
        
        return ['min' => $min, 'max' => $max];
    }
}