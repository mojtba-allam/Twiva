<?php

namespace Modules\Order\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Order\app\Models\Order;
use Modules\User\app\Models\User;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $users = User::all();

        // Create orders
        Order::factory(50)->create([
            'user_id' => fn() => $users->random()->id
        ]);
    }
}