<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 users
        \App\Models\User::factory(100)->create();
        \App\Models\Admin::factory(10)->create();
        \App\Models\Categories::factory(10)->create();
        \App\Models\Order::factory(50)->create();
        \App\Models\Products::factory(100)->create();

    }
}
