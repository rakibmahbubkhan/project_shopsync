<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\SaleItem;

class SalesTableSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();
        $products = Product::all();
        $warehouses = Warehouse::where('is_active', true)->get();

        foreach (range(1, 20) as $index) {
            $customer = $customers->random();
            $user = $users->random();
            $warehouse = $warehouses->random();
            
            $totalAmount = 0;
            $items = [];

            // Generate 1-6 items per sale
            foreach (range(1, rand(1, 6)) as $itemIndex) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $sellingPrice = $product->selling_price;
                $costPrice = $product->cost_price;
                $subtotal = $quantity * $sellingPrice;
                $grossProfit = ($sellingPrice - $costPrice) * $quantity;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'selling_price' => $sellingPrice,
                    'cost_price' => $costPrice,
                    'gross_profit' => $grossProfit,
                    'subtotal' => $subtotal,
                ];
                
                $totalAmount += $subtotal;
            }

            $discount = $totalAmount > 100 ? rand(5, 20) : 0;
            $tax = $totalAmount * 0.1; // 10% tax
            $grandTotal = $totalAmount - $discount + $tax;
            $totalCogs = collect($items)->sum('cost_price');
            $grossProfit = $totalAmount - $totalCogs;

            $paymentMethods = ['cash', 'card', 'bank', 'mobile'];
            $paymentStatuses = ['pending', 'partial', 'paid'];

            Sale::create([
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'created_by' => $user->id,
                'total_amount' => $grandTotal,
                'discount' => $discount,
                'tax' => $tax,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                'sale_date' => now()->subDays(rand(1, 90)),
                'total_cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
            ]);
        }
    }
}