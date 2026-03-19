<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Product;
use App\Models\PurchaseItem;

class PurchasesTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $users = User::all();
        $products = Product::all();

        foreach (range(1, 10) as $index) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();
            
            $totalAmount = 0;
            $items = [];

            // Generate 2-5 items per purchase
            foreach (range(1, rand(2, 5)) as $itemIndex) {
                $product = $products->random();
                $quantity = rand(5, 50);
                $costPrice = $product->cost_price;
                $subtotal = $quantity * $costPrice;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                ];
                
                $totalAmount += $subtotal;
            }

            $paidAmount = $totalAmount * (rand(0, 100) / 100);
            $paymentStatus = 'unpaid';
            if ($paidAmount == $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $statuses = ['ordered', 'received', 'pending'];
            $status = $statuses[array_rand($statuses)];

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'purchase_date' => now()->subDays(rand(1, 60)),
                'reference_no' => 'PO-' . str_pad($index, 5, '0', STR_PAD_LEFT),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'created_by' => $user->id,
            ]);

            foreach ($items as $item) {
                PurchaseItem::create(array_merge($item, ['purchase_id' => $purchase->id]));
            }
        }
    }
}