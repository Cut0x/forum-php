<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Category::uniqueSlug($name),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_readonly' => false,
            'is_pinned' => false,
        ];
    }
}
