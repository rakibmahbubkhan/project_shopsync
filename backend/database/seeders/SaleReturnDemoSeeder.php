<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleReturnDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating demo sale returns...');

        // Get admin user
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $manager = User::where('role', 'manager')->first() ?? User::first();

        if (!$admin || !$manager) {
            $this->command->error('Required users not found. Please seed users first.');
            return;
        }

        // Get some sales
        $sales = Sale::with(['items.product', 'customer'])->take(10)->get();

        if ($sales->isEmpty()) {
            $this->command->error('No sales found. Please seed sales first.');
            return;
        }

        DB::transaction(function () use ($sales, $admin, $manager) {
            
            // ==================== SCENARIO 1: Completed Return with Cash Refund ====================
            $sale1 = $sales->get(0);
            if ($sale1 && $sale1->items->isNotEmpty()) {
                $item = $sale1->items->first();
                $returnQty = min(1, $item->quantity);
                $refundAmount = $returnQty * $item->selling_price;

                $return1 = SaleReturn::create([
                    'sale_id' => $sale1->id,
                    'user_id' => $manager->id,
                    'return_date' => Carbon::now()->subDays(5),
                    'reason' => 'Product had manufacturing defect',
                    'total_amount' => $refundAmount,
                    'status' => 'completed',
                    'notes' => 'Customer showed video proof of defect.',
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now()->subDays(4),
                ]);

                SaleReturnItem::create([
                    'sale_return_id' => $return1->id,
                    'product_id' => $item->product_id,
                    'quantity' => $returnQty,
                    'selling_price' => $item->selling_price,
                    'cost_price' => $item->cost_price,
                    'subtotal' => $refundAmount,
                ]);

                Refund::create([
                    'sale_return_id' => $return1->id,
                    'amount' => $refundAmount,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                    'processed_by' => $admin->id,
                ]);

                $this->command->info("✅ Created completed return #{$return1->id} - Cash Refund");
            }

            // ==================== SCENARIO 2: Pending Return (Needs Approval) ====================
            $sale2 = $sales->get(1);
            if ($sale2 && $sale2->items->count() >= 2) {
                $item2 = $sale2->items->get(0);
                $item3 = $sale2->items->get(1);
                $returnQty2 = min(1, $item2->quantity);
                $returnQty3 = min(1, $item3->quantity);
                $refundAmount2 = ($returnQty2 * $item2->selling_price) + ($returnQty3 * $item3->selling_price);

                $return2 = SaleReturn::create([
                    'sale_id' => $sale2->id,
                    'user_id' => $manager->id,
                    'return_date' => Carbon::now()->subDays(1),
                    'reason' => 'Wrong items delivered - customer ordered different variants',
                    'total_amount' => $refundAmount2,
                    'status' => 'pending',
                    'notes' => 'Large refund amount - requires manager approval.',
                ]);

                SaleReturnItem::create([
                    'sale_return_id' => $return2->id,
                    'product_id' => $item2->product_id,
                    'quantity' => $returnQty2,
                    'selling_price' => $item2->selling_price,
                    'cost_price' => $item2->cost_price,
                    'subtotal' => $returnQty2 * $item2->selling_price,
                ]);

                SaleReturnItem::create([
                    'sale_return_id' => $return2->id,
                    'product_id' => $item3->product_id,
                    'quantity' => $returnQty3,
                    'selling_price' => $item3->selling_price,
                    'cost_price' => $item3->cost_price,
                    'subtotal' => $returnQty3 * $item3->selling_price,
                ]);

                $this->command->info("✅ Created pending return #{$return2->id} - Needs Approval");
            }

            // ==================== SCENARIO 3: Approved Return with Card Refund ====================
            $sale3 = $sales->get(2);
            if ($sale3 && $sale3->items->isNotEmpty()) {
                $item4 = $sale3->items->first();
                $returnQty4 = min(2, $item4->quantity);
                $refundAmount4 = $returnQty4 * $item4->selling_price;

                $return3 = SaleReturn::create([
                    'sale_id' => $sale3->id,
                    'user_id' => $manager->id,
                    'return_date' => Carbon::now()->subDays(3),
                    'reason' => 'Customer found better price elsewhere',
                    'total_amount' => $refundAmount4,
                    'status' => 'approved',
                    'notes' => 'Approved as goodwill gesture.',
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now()->subDays(2),
                ]);

                SaleReturnItem::create([
                    'sale_return_id' => $return3->id,
                    'product_id' => $item4->product_id,
                    'quantity' => $returnQty4,
                    'selling_price' => $item4->selling_price,
                    'cost_price' => $item4->cost_price,
                    'subtotal' => $refundAmount4,
                ]);

                Refund::create([
                    'sale_return_id' => $return3->id,
                    'amount' => $refundAmount4,
                    'payment_method' => 'card',
                    'reference_number' => 'CRD-' . str_pad($return3->id, 6, '0', STR_PAD_LEFT),
                    'status' => 'completed',
                    'processed_by' => $admin->id,
                ]);

                $this->command->info("✅ Created approved return #{$return3->id} - Card Refund");
            }

            // ==================== SCENARIO 4: Full Invoice Return ====================
            $sale4 = $sales->get(3);
            if ($sale4 && $sale4->items->isNotEmpty()) {
                $totalRefund4 = 0;
                
                $return4 = SaleReturn::create([
                    'sale_id' => $sale4->id,
                    'user_id' => $manager->id,
                    'return_date' => Carbon::now()->subDays(10),
                    'reason' => 'Customer received damaged package - all items affected',
                    'total_amount' => 0, // Will update
                    'status' => 'completed',
                    'notes' => 'Shipping company confirmed damage. Insurance claim filed.',
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now()->subDays(9),
                ]);

                foreach ($sale4->items as $saleItem) {
                    $returnQty = $saleItem->quantity;
                    $itemRefund = $returnQty * $saleItem->selling_price;
                    $totalRefund4 += $itemRefund;

                    SaleReturnItem::create([
                        'sale_return_id' => $return4->id,
                        'product_id' => $saleItem->product_id,
                        'quantity' => $returnQty,
                        'selling_price' => $saleItem->selling_price,
                        'cost_price' => $saleItem->cost_price,
                        'subtotal' => $itemRefund,
                    ]);
                }

                $return4->update(['total_amount' => $totalRefund4]);

                Refund::create([
                    'sale_return_id' => $return4->id,
                    'amount' => $totalRefund4,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'BNK-' . str_pad($return4->id, 6, '0', STR_PAD_LEFT),
                    'status' => 'completed',
                    'processed_by' => $admin->id,
                ]);

                $this->command->info("✅ Created full return #{$return4->id} - Bank Transfer Refund");
            }

            // ==================== SCENARIO 5: Rejected Return ====================
            $sale5 = $sales->get(4);
            if ($sale5 && $sale5->items->isNotEmpty()) {
                $item5 = $sale5->items->first();
                $refundAmount5 = 1 * $item5->selling_price;

                $return5 = SaleReturn::create([
                    'sale_id' => $sale5->id,
                    'user_id' => $manager->id,
                    'return_date' => Carbon::now()->subDays(7),
                    'reason' => 'Customer claimed item not working',
                    'total_amount' => $refundAmount5,
                    'status' => 'rejected',
                    'notes' => 'Rejected - Item tested and working properly. No defect found.',
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now()->subDays(6),
                ]);

                SaleReturnItem::create([
                    'sale_return_id' => $return5->id,
                    'product_id' => $item5->product_id,
                    'quantity' => 1,
                    'selling_price' => $item5->selling_price,
                    'cost_price' => $item5->cost_price,
                    'subtotal' => $refundAmount5,
                ]);

                $this->command->info("✅ Created rejected return #{$return5->id}");
            }
        });

        // Display Summary
        $this->command->newLine();
        $this->command->info('📊 Sale Returns Summary:');
        $this->command->table(
            ['Status', 'Count', 'Total Amount'],
            [
                ['Pending', SaleReturn::where('status', 'pending')->count(), 
                 '৳' . number_format(SaleReturn::where('status', 'pending')->sum('total_amount'), 2)],
                ['Approved', SaleReturn::where('status', 'approved')->count(), 
                 '৳' . number_format(SaleReturn::where('status', 'approved')->sum('total_amount'), 2)],
                ['Completed', SaleReturn::where('status', 'completed')->count(), 
                 '৳' . number_format(SaleReturn::where('status', 'completed')->sum('total_amount'), 2)],
                ['Rejected', SaleReturn::where('status', 'rejected')->count(), 
                 '৳' . number_format(SaleReturn::where('status', 'rejected')->sum('total_amount'), 2)],
                ['TOTAL', SaleReturn::count(), 
                 '৳' . number_format(SaleReturn::sum('total_amount'), 2)],
            ]
        );

        $this->command->info('Demo sale returns created successfully!');
    }
}