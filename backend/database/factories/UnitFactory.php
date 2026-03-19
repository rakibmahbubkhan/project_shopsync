<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = [
            ['Piece', 'pc'],
            ['Kilogram', 'kg'],
            ['Gram', 'g'],
            ['Liter', 'L'],
            ['Meter', 'm'],
            ['Box', 'box'],
            ['Pack', 'pack'],
            ['Dozen', 'dz'],
        ];
        
        $unit = fake()->unique()->randomElement($units);
        
        return [
            'name' => $unit[0],
            'short_name' => $unit[1],
        ];
    }
}