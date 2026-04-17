<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;

class PurchasesTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $users = User::all();
        $products = Product::all();

        if ($suppliers->isEmpty() || $warehouses->isEmpty() || $users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Please seed suppliers, warehouses, users, and products first.');
            return;
        }

        foreach (range(1, 20) as $index) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();
            
            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalAmount = 0;
            $items = [];

            // Generate 2-5 items per purchase
            $numItems = rand(2, 5);
            
            foreach (range(1, $numItems) as $itemIndex) {
                $product = $products->random();
                $quantity = rand(5, 50);
                $purchasePrice = $product->cost_price ?? rand(50, 500);
                
                // Random discount (0-15%)
                $discountPercent = rand(0, 15);
                $discountAmount = ($quantity * $purchasePrice * $discountPercent) / 100;
                
                // Random tax (0-10%)
                $taxPercent = rand(0, 10);
                $taxableAmount = ($quantity * $purchasePrice) - $discountAmount;
                $taxAmount = ($taxableAmount * $taxPercent) / 100;
                
                $itemSubtotal = $quantity * $purchasePrice;
                $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'subtotal' => $itemSubtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'total' => $itemTotal,
                    'batch_no' => 'BATCH-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
                    'received_quantity' => 0,
                    'returned_quantity' => 0,
                    'damaged_quantity' => 0,
                ];
                
                $subtotal += $itemSubtotal;
                $totalDiscount += $discountAmount;
                $totalTax += $taxAmount;
                $totalAmount += $itemTotal;
            }

            // Determine paid amount and payment status
            $paymentScenarios = [
                'unpaid' => 0,
                'partial' => $totalAmount * (rand(10, 90) / 100),
                'paid' => $totalAmount
            ];
            
            $paymentStatus = array_rand($paymentScenarios);
            $paidAmount = $paymentScenarios[$paymentStatus];
            
            // Round to 2 decimal places
            $paidAmount = round($paidAmount, 2);
            
            // Status scenarios
            $statuses = ['ordered', 'received', 'pending'];
            $status = $statuses[array_rand($statuses)];
            
            // If status is received, mark some items as received
            if ($status === 'received') {
                foreach ($items as &$item) {
                    $item['received_quantity'] = $item['quantity'];
                }
            } elseif ($status === 'ordered' && rand(0, 1)) {
                // Partial receipt for ordered items
                foreach ($items as &$item) {
                    $receivedQty = round($item['quantity'] * (rand(30, 90) / 100), 0);
                    $item['received_quantity'] = $receivedQty;
                }
            }
            
            // Shipping information
            $shippingMethods = ['Standard', 'Express', 'Air', 'Sea', 'Courier'];
            $paymentMethods = ['cash', 'bank_transfer', 'check', 'mobile_banking'];
            
            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'purchase_date' => now()->subDays(rand(1, 90)),
                'reference_no' => 'PO-' . date('Y') . date('m') . str_pad($index, 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'notes' => 'Sample purchase order - ' . fake()->sentence(),
                'shipping_method' => $shippingMethods[array_rand($shippingMethods)],
                'shipping_cost' => rand(0, 500),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'expected_delivery_date' => now()->addDays(rand(3, 15))->format('Y-m-d'),
                'delivered_date' => $status === 'received' ? now()->subDays(rand(1, 10))->format('Y-m-d') : null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);

            // Create purchase items
            foreach ($items as $item) {
                PurchaseItem::create(array_merge($item, ['purchase_id' => $purchase->id]));
            }
            
            // Create payment installments for partial or paid purchases
            if ($paymentStatus !== 'unpaid' && $paidAmount > 0) {
                $remainingAmount = $paidAmount;
                $installmentNumber = 1;
                
                while ($remainingAmount > 0) {
                    // Determine installment amount (for partial payments, split into 1-3 installments)
                    if ($paymentStatus === 'partial' && $remainingAmount < $paidAmount) {
                        $installmentAmount = round($remainingAmount, 2);
                        $remainingAmount = 0;
                    } else {
                        if ($paymentStatus === 'paid') {
                            // For paid purchases, split into 1-3 installments
                            $numInstallments = rand(1, 3);
                            $installmentAmount = round($paidAmount / $numInstallments, 2);
                            if ($installmentNumber == $numInstallments) {
                                $installmentAmount = round($remainingAmount, 2);
                            }
                        } else {
                            // For partial, create 1-2 installments
                            $installmentAmount = round($remainingAmount * rand(40, 100) / 100, 2);
                        }
                        
                        $remainingAmount = round($remainingAmount - $installmentAmount, 2);
                        if ($remainingAmount < 0.01) {
                            $installmentAmount += $remainingAmount;
                            $remainingAmount = 0;
                        }
                    }
                    
                    if ($installmentAmount > 0) {
                        PurchasePayment::create([
                            'purchase_id' => $purchase->id,
                            'amount' => $installmentAmount,
                            'payment_date' => $purchase->purchase_date->addDays(rand(0, 30)),
                            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                            'reference_no' => 'PAY-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                            'notes' => 'Installment #' . $installmentNumber,
                            'installment_number' => $installmentNumber,
                            'created_by' => $user->id,
                            'created_at' => $purchase->purchase_date->addDays(rand(0, 30)),
                        ]);
                        
                        $installmentNumber++;
                    }
                }
            }
        }
        
        // Create some additional purchases with specific scenarios
        $this->createOverduePurchase($suppliers, $warehouses, $users, $products);
        $this->createHighValuePurchase($suppliers, $warehouses, $users, $products);
        $this->createFullyReceivedPurchase($suppliers, $warehouses, $users, $products);
        $this->createPartiallyReceivedPurchase($suppliers, $warehouses, $users, $products);
    }
    
    private function createOverduePurchase($suppliers, $warehouses, $users, $products)
    {
        $supplier = $suppliers->random();
        $warehouse = $warehouses->random();
        $user = $users->random();
        
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalAmount = 0;
        $items = [];
        
        foreach (range(1, 3) as $index) {
            $product = $products->random();
            $quantity = rand(10, 30);
            $purchasePrice = $product->cost_price ?? rand(100, 300);
            
            $discountPercent = 5;
            $discountAmount = ($quantity * $purchasePrice * $discountPercent) / 100;
            $taxPercent = 10;
            $taxableAmount = ($quantity * $purchasePrice) - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            
            $itemSubtotal = $quantity * $purchasePrice;
            $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'subtotal' => $itemSubtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $itemTotal,
                'batch_no' => 'BATCH-OVD-' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addMonths(12),
                'received_quantity' => 0,
                'returned_quantity' => 0,
                'damaged_quantity' => 0,
            ];
            
            $subtotal += $itemSubtotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
            $totalAmount += $itemTotal;
        }
        
        Purchase::create([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->subDays(45),
            'reference_no' => 'PO-OVD-' . date('Ymd'),
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'status' => 'ordered',
            'notes' => 'Overdue purchase - payment pending for 45+ days',
            'shipping_method' => 'Express',
            'shipping_cost' => 150,
            'payment_method' => null,
            'expected_delivery_date' => now()->subDays(30),
            'delivered_date' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
    
    private function createHighValuePurchase($suppliers, $warehouses, $users, $products)
    {
        $supplier = $suppliers->random();
        $warehouse = $warehouses->random();
        $user = $users->random();
        
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalAmount = 0;
        $items = [];
        
        foreach (range(1, 8) as $index) {
            $product = $products->random();
            $quantity = rand(50, 200);
            $purchasePrice = $product->cost_price ?? rand(500, 2000);
            
            $discountPercent = 10;
            $discountAmount = ($quantity * $purchasePrice * $discountPercent) / 100;
            $taxPercent = 15;
            $taxableAmount = ($quantity * $purchasePrice) - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            
            $itemSubtotal = $quantity * $purchasePrice;
            $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'subtotal' => $itemSubtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $itemTotal,
                'batch_no' => 'BATCH-HV-' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addMonths(18),
                'received_quantity' => 0,
                'returned_quantity' => 0,
                'damaged_quantity' => 0,
            ];
            
            $subtotal += $itemSubtotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
            $totalAmount += $itemTotal;
        }
        
        Purchase::create([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->subDays(15),
            'reference_no' => 'PO-HV-' . date('Ymd'),
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
            'paid_amount' => round($totalAmount * 0.3, 2),
            'payment_status' => 'partial',
            'status' => 'ordered',
            'notes' => 'High value purchase - 30% paid',
            'shipping_method' => 'Air',
            'shipping_cost' => 500,
            'payment_method' => 'bank_transfer',
            'expected_delivery_date' => now()->addDays(7),
            'delivered_date' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
    
    private function createFullyReceivedPurchase($suppliers, $warehouses, $users, $products)
    {
        $supplier = $suppliers->random();
        $warehouse = $warehouses->random();
        $user = $users->random();
        
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalAmount = 0;
        $items = [];
        
        foreach (range(1, 4) as $index) {
            $product = $products->random();
            $quantity = rand(20, 100);
            $purchasePrice = $product->cost_price ?? rand(50, 150);
            
            $discountPercent = 8;
            $discountAmount = ($quantity * $purchasePrice * $discountPercent) / 100;
            $taxPercent = 8;
            $taxableAmount = ($quantity * $purchasePrice) - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            
            $itemSubtotal = $quantity * $purchasePrice;
            $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'subtotal' => $itemSubtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $itemTotal,
                'batch_no' => 'BATCH-FR-' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addMonths(12),
                'received_quantity' => $quantity, // Fully received
                'returned_quantity' => 0,
                'damaged_quantity' => 0,
            ];
            
            $subtotal += $itemSubtotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
            $totalAmount += $itemTotal;
        }
        
        Purchase::create([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->subDays(30),
            'reference_no' => 'PO-FR-' . date('Ymd'),
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
            'paid_amount' => $totalAmount,
            'payment_status' => 'paid',
            'status' => 'received',
            'notes' => 'Fully received and paid purchase',
            'shipping_method' => 'Standard',
            'shipping_cost' => 200,
            'payment_method' => 'cash',
            'expected_delivery_date' => now()->subDays(25),
            'delivered_date' => now()->subDays(28),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
    
    private function createPartiallyReceivedPurchase($suppliers, $warehouses, $users, $products)
    {
        $supplier = $suppliers->random();
        $warehouse = $warehouses->random();
        $user = $users->random();
        
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalAmount = 0;
        $items = [];
        
        foreach (range(1, 3) as $index) {
            $product = $products->random();
            $quantity = rand(30, 80);
            $purchasePrice = $product->cost_price ?? rand(80, 200);
            
            $discountPercent = 5;
            $discountAmount = ($quantity * $purchasePrice * $discountPercent) / 100;
            $taxPercent = 12;
            $taxableAmount = ($quantity * $purchasePrice) - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            
            $itemSubtotal = $quantity * $purchasePrice;
            $itemTotal = $itemSubtotal - $discountAmount + $taxAmount;
            
            // Partially received - 60% received
            $receivedQty = round($quantity * 0.6);
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'subtotal' => $itemSubtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $itemTotal,
                'batch_no' => 'BATCH-PR-' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addMonths(10),
                'received_quantity' => $receivedQty,
                'returned_quantity' => 0,
                'damaged_quantity' => 0,
            ];
            
            $subtotal += $itemSubtotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
            $totalAmount += $itemTotal;
        }
        
        Purchase::create([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->subDays(20),
            'reference_no' => 'PO-PR-' . date('Ymd'),
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
            'paid_amount' => round($totalAmount * 0.5, 2),
            'payment_status' => 'partial',
            'status' => 'ordered',
            'notes' => 'Partially received - 60% of items received, 50% paid',
            'shipping_method' => 'Courier',
            'shipping_cost' => 100,
            'payment_method' => 'mobile_banking',
            'expected_delivery_date' => now()->subDays(5),
            'delivered_date' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}