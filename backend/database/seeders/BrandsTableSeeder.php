<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandsTableSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Samsung'],
            ['name' => 'Apple'],
            ['name' => 'Sony'],
            ['name' => 'LG'],
            ['name' => 'Nike'],
            ['name' => 'Adidas'],
            ['name' => 'Puma'],
            ['name' => 'Dell'],
            ['name' => 'HP'],
            ['name' => 'Canon'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}