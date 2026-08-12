<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'image' => 'https://placehold.co/400x300/111111/FFFFFF?text=Food',
            'price' => fake()->randomFloat(2, 2, 25),
            'is_available' => true,
            'preparation_time' => fake()->numberBetween(5, 30),
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
