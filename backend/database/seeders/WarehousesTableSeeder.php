<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehousesTableSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'address' => '1000 Industrial Pkwy, City',
                'is_active' => true,
            ],
            [
                'name' => 'North Branch',
                'code' => 'WH002',
                'address' => '200 North St, North City',
                'is_active' => true,
            ],
            [
                'name' => 'South Warehouse',
                'code' => 'WH003',
                'address' => '300 South Ave, South Town',
                'is_active' => true,
            ],
            [
                'name' => 'East Distribution Center',
                'code' => 'WH004',
                'address' => '400 East Blvd, East City',
                'is_active' => false,
            ],
            [
                'name' => 'West Storage',
                'code' => 'WH005',
                'address' => '500 West Rd, West Village',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}   