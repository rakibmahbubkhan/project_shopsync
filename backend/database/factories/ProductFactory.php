<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        $brands = Brand::pluck('id')->toArray();
        $units = Unit::pluck('id')->toArray();
        
        return [
            'name' => fake()->unique()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'barcode' => fake()->unique()->ean13(),
            'category_id' => fake()->randomElement($categories),
            'brand_id' => fake()->optional(0.7)->randomElement($brands),
            'unit_id' => fake()->randomElement($units),
            'cost_price' => fake()->randomFloat(2, 10, 500),
            'selling_price' => fake()->randomFloat(2, 20, 1000),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'alert_quantity' => fake()->numberBetween(5, 20),
            'image' => null,
            'status' => fake()->boolean(90),
        ];
    }
}