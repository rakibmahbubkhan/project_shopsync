<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Distributors',
                'email' => 'info@techdist.com',
                'phone' => '1112223333',
                'address' => '100 Tech Park, Silicon Valley',
            ],
            [
                'name' => 'Fashion Wholesale',
                'email' => 'sales@fashionwholesale.com',
                'phone' => '4445556666',
                'address' => '200 Fashion Ave, New York',
            ],
            [
                'name' => 'Food Suppliers Inc',
                'email' => 'contact@foodsuppliers.com',
                'phone' => '7778889999',
                'address' => '300 Food St, Chicago',
            ],
            [
                'name' => 'Furniture Mart',
                'email' => 'orders@furnituremart.com',
                'phone' => '1231231234',
                'address' => '400 Furniture Row, Dallas',
            ],
            [
                'name' => 'Book Distributors',
                'email' => 'info@bookdist.com',
                'phone' => '5554443333',
                'address' => '500 Book Lane, Boston',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}