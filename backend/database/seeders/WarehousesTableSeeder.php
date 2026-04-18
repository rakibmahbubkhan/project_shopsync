<?php
// database/seeders/WarehousesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehousesTableSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'WH-0001',
                'name' => 'Main Warehouse',
                'address' => '123 Industrial Area, Sector 1, Dhaka',
                'capacity' => 10000,
                'is_active' => true,
            ],
            [
                'code' => 'WH-0002',
                'name' => 'Chittagong Warehouse',
                'address' => '45 Port Road, Chittagong',
                'capacity' => 8000,
                'is_active' => true,
            ],
            [
                'code' => 'WH-0003',
                'name' => 'Rajshahi Warehouse',
                'address' => '78 Station Road, Rajshahi',
                'capacity' => 5000,
                'is_active' => true,
            ],
            [
                'code' => 'WH-0004',
                'name' => 'Khulna Warehouse',
                'address' => '12 KDA Avenue, Khulna',
                'capacity' => 4000,
                'is_active' => true,
            ],
            [
                'code' => 'WH-0005',
                'name' => 'Sylhet Warehouse',
                'address' => '56 Zindabazar, Sylhet',
                'capacity' => 3500,
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }

        $this->command->info('Warehouses seeded successfully!');
        $this->command->info('Total warehouses: ' . Warehouse::count());
    }
}