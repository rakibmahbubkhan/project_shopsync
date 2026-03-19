<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Check if Customer model uses factory
        if (method_exists(Customer::class, 'factory')) {
            Customer::factory()->count(20)->create();
        }

        // Create specific customers
        $customers = [
            [
                'name' => 'Walk-in Customer',
                'email' => 'walkin@example.com',
                'phone' => '0000000000',
                'address' => 'Default Customer',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St, City',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '0987654321',
                'address' => '456 Oak Ave, Town',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'phone' => '5551234567',
                'address' => '789 Pine Rd, Village',
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'phone' => '7778889999',
                'address' => '321 Elm Blvd, City',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}