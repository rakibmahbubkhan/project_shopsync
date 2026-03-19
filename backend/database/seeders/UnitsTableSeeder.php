<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitsTableSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'pc'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Liter', 'short_name' => 'L'],
            ['name' => 'Milliliter', 'short_name' => 'ml'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Centimeter', 'short_name' => 'cm'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Dozen', 'short_name' => 'dz'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}