<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Categories;
use App\Models\BusinessAccount;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
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
            'product_url' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'business_account_id' => BusinessAccount::factory(),
            'category_id' => Categories::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($product) {
            $product->update([
                'product_url' => url("/products/{$product->id}")
            ]);
        });
    }
}
