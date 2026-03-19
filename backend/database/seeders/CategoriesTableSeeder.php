<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Apparel and fashion items'],
            ['name' => 'Food & Beverages', 'description' => 'Edible items and drinks'],
            ['name' => 'Furniture', 'description' => 'Home and office furniture'],
            ['name' => 'Books', 'description' => 'Books and stationery'],
            ['name' => 'Sports', 'description' => 'Sports equipment and accessories'],
            ['name' => 'Toys', 'description' => 'Children toys and games'],
            ['name' => 'Beauty', 'description' => 'Beauty and personal care products'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}