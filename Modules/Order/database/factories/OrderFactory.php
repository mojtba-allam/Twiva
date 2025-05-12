<?php

namespace Modules\Order\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\User\app\Models\User;
use Modules\Product\app\Models\Product;
use Modules\Order\app\Models\Order;

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
    protected $model = Order::class;
    public function definition(): array
    {
        // Get random products
        $products = Product::inRandomOrder()->limit(3)->get();

        // Create products list
        $products_list = $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(1, 5)
            ];
        })->toArray();

        // Calculate total price
        $total_price = 0;
        foreach ($products_list as $item) {
            $product = Product::find($item['product_id']);
            $total_price += $product->price * $item['quantity'];
        }

        return [
            'user_id' => User::factory(),
            'products_list' => json_encode($products_list),
            'total_quantity' => collect($products_list)->sum('quantity'),
            'total_price' => $total_price,
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
