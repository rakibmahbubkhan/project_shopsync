<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $suppliers = Supplier::pluck('id')->toArray();
        $warehouses = Warehouse::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();
        
        $totalAmount = fake()->randomFloat(2, 100, 10000);
        $paidAmount = fake()->randomFloat(2, 0, $totalAmount);
        
        $paymentStatus = 'unpaid';
        if ($paidAmount == $totalAmount) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }
        
        return [
            'supplier_id' => fake()->randomElement($suppliers),
            'warehouse_id' => fake()->randomElement($warehouses),
            'purchase_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'reference_no' => fake()->unique()->bothify('PO-#####'),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'status' => fake()->randomElement(['ordered', 'received', 'pending']),
            'created_by' => fake()->randomElement($users),
        ];
    }
}