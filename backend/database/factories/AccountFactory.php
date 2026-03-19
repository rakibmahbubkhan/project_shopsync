<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['asset', 'liability', 'equity', 'income', 'expense'];
        
        return [
            'code' => fake()->unique()->numerify('####'),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement($types),
        ];
    }
}