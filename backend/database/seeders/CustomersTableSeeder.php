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
                'phone_number' => '0000000000',
                'billing_address' => 'Default Customer',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone_number' => '1234567890',
                'billing_address' => '123 Main St, City',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone_number' => '0987654321',
                'billing_address' => '456 Oak Ave, Town',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'phone_number' => '5551234567',
                'billing_address' => '789 Pine Rd, Village',
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'phone_number' => '7778889999',
                'billing_address' => '321 Elm Blvd, City',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}