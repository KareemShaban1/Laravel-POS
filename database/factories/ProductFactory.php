<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        $has_quantity = fake()->boolean(50);
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(100, 1000),
            'barcode' => fake()->ean13(),
            'has_quantity' => $has_quantity,
            'quantity' => $has_quantity ? fake()->numberBetween(1, 100) : 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
