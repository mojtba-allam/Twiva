<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Products;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create products and get their full records
        $products = Products::factory()->count(3)->create();

        // Create products list with quantities
        $products_list = $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(1, 5) // Random quantity for each product
            ];
        })->toArray();

        return [
            'user_id' => User::factory(),
            'products_list' => json_encode($products_list),
            'total_quantity' => collect($products_list)->sum('quantity'),

            'total_price' => $products->sum(
                function ($product) use ($products_list) {
                $quantity = collect($products_list)
                    ->firstWhere('product_id', $product->id)['quantity'];
                return $product->price * $quantity;
            }),

            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
