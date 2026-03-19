<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class ProductStocksTableSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $warehouses = Warehouse::where('is_active', true)->get();

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                // Distribute the product's stock quantity across warehouses
                $warehouseQuantity = floor($product->stock_quantity / $warehouses->count());
                if ($warehouseQuantity > 0) {
                    DB::table('product_stocks')->insert([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $warehouseQuantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}