<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;

class SaleItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::all();
        $products = Product::all();

        foreach ($sales as $sale) {
            $itemCount = rand(1, 6);
            $usedProducts = [];

            for ($i = 0; $i < $itemCount; $i++) {
                $product = $products->except($usedProducts)->random();
                $usedProducts[] = $product->id;

                $quantity = rand(1, 10);
                $sellingPrice = $product->selling_price;
                $costPrice = $product->cost_price;
                $subtotal = $quantity * $sellingPrice;
                $grossProfit = ($sellingPrice - $costPrice) * $quantity;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'selling_price' => $sellingPrice,
                    'cost_price' => $costPrice,
                    'gross_profit' => $grossProfit,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }
}