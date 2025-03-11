<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Categories;
use App\Models\Order;
use App\Models\Products;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admins first
        $admins = Admin::factory(10)->create();

        // Create users
        $users = User::factory(100)->create();

        // Create categories using existing admins
        $categories = Categories::factory(10)->create([
            'admin_id' => fn() => $admins->random()->id
        ]);

        // Create products using existing admins and categories
        $products = Products::factory(100)->create([
            'admin_id' => fn() => $admins->random()->id,
            'category_id' => fn() => $categories->random()->id
        ]);

        // Create orders without product_id field
        Order::factory(50)->create([
            'user_id' => fn() => $users->random()->id
        ]);
    }
}
