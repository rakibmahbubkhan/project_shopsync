<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use App\Models\User;
use App\Models\Warehouse;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customers = Customer::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();
        $warehouses = Warehouse::where('is_active', true)->pluck('id')->toArray();
        
        return [
            'customer_id' => fake()->randomElement($customers),
            'warehouse_id' => fake()->randomElement($warehouses),
            'created_by' => fake()->randomElement($users),
            'total_amount' => fake()->randomFloat(2, 50, 5000),
            'discount' => fake()->randomFloat(2, 0, 200),
            'tax' => fake()->randomFloat(2, 0, 500),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank', 'mobile']),
            'payment_status' => fake()->randomElement(['pending', 'partial', 'paid']),
            'sale_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'total_cogs' => fake()->randomFloat(2, 30, 4000),
            'gross_profit' => fake()->randomFloat(2, 20, 1000),
        ];
    }
}