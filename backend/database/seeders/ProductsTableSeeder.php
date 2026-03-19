<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();

        $products = [
            [
                'name' => 'Smartphone X',
                'sku' => 'PHN001',
                'barcode' => '123456789012',
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'brand_id' => $brands->where('name', 'Samsung')->first()->id,
                'unit_id' => $units->where('short_name', 'pc')->first()->id,
                'cost_price' => 500.00,
                'selling_price' => 699.99,
                'stock_quantity' => 50,
                'alert_quantity' => 5,
                'status' => true,
            ],
            [
                'name' => 'Laptop Pro',
                'sku' => 'LPT001',
                'barcode' => '234567890123',
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'brand_id' => $brands->where('name', 'Dell')->first()->id,
                'unit_id' => $units->where('short_name', 'pc')->first()->id,
                'cost_price' => 800.00,
                'selling_price' => 999.99,
                'stock_quantity' => 30,
                'alert_quantity' => 3,
                'status' => true,
            ],
            [
                'name' => 'Running Shoes',
                'sku' => 'SHO001',
                'barcode' => '345678901234',
                'category_id' => $categories->where('name', 'Sports')->first()->id,
                'brand_id' => $brands->where('name', 'Nike')->first()->id,
                'unit_id' => $units->where('short_name', 'pc')->first()->id,
                'cost_price' => 40.00,
                'selling_price' => 79.99,
                'stock_quantity' => 100,
                'alert_quantity' => 10,
                'status' => true,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'sku' => 'CLT001',
                'barcode' => '456789012345',
                'category_id' => $categories->where('name', 'Clothing')->first()->id,
                'brand_id' => $brands->where('name', 'Adidas')->first()->id,
                'unit_id' => $units->where('short_name', 'pc')->first()->id,
                'cost_price' => 10.00,
                'selling_price' => 24.99,
                'stock_quantity' => 200,
                'alert_quantity' => 20,
                'status' => true,
            ],
            [
                'name' => 'Office Chair',
                'sku' => 'FUR001',
                'barcode' => '567890123456',
                'category_id' => $categories->where('name', 'Furniture')->first()->id,
                'brand_id' => null,
                'unit_id' => $units->where('short_name', 'pc')->first()->id,
                'cost_price' => 80.00,
                'selling_price' => 149.99,
                'stock_quantity' => 25,
                'alert_quantity' => 3,
                'status' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Generate additional products using factory
        Product::factory()->count(30)->create();
    }
}