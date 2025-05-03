<?php

namespace Modules\Product\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\app\Models\Category;
use Modules\Business\app\Models\Business;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Product\app\Models\Product>
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
        return [
            'title' => fake()->name(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 100),
            'quantity' => "100",
            'image_url' => fake()->imageUrl(),
            'created_at' => now(),
            'updated_at' => now(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'rejection_reason' => fake()->sentence(),
            'business_account_id' => Business::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
