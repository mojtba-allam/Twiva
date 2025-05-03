<?php

namespace Modules\Product\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\app\Models\Product;
use Modules\Business\app\Models\Business;
use Modules\Category\app\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get business accounts and categories
        $businessAccounts = Business::all();
        $categories = Category::all();

        // Create products
        Product::factory(100)->create([
            'business_account_id' => fn() => $businessAccounts->random()->id,
            'category_id' => fn() => $categories->random()->id
        ]);
    }
}