<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Categories;
use App\Models\Order;
use App\Models\Products;
use App\Models\BusinessAccount;
use App\Models\Notification;

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

        // Create business accounts
        $businessAccounts = BusinessAccount::factory(10)->create();

        // Create categories using existing admins
        $categories = Categories::factory(10)->create([
            'admin_id' => fn() => $admins->random()->id
        ]);

        // Create products using existing admins and categories
        $products = Products::factory(100)->create([
            'business_account_id' => fn() => $businessAccounts->random()->id,
            'category_id' => fn() => $categories->random()->id
        ]);

        // Create orders without product_id field
        Order::factory(50)->create([
            'user_id' => fn() => $users->random()->id
        ]);

        // Create notifications for all users
        $this->seedNotifications($admins, $users, $businessAccounts);
    }

    /**
     * Seed notifications for all user types
     */
    private function seedNotifications($admins, $users, $businessAccounts): void
    {
        // Create notifications for admins
        foreach ($admins as $admin) {
            $this->createNotifications(Admin::class, $admin->id, 5);
        }

        // Create notifications for business accounts
        foreach ($businessAccounts as $business) {
            $this->createNotifications(BusinessAccount::class, $business->id, 5);
        }

        // Create notifications for users
        foreach ($users as $user) {
            $this->createNotifications(User::class, $user->id, 5);
        }
    }

    /**
     * Create a specified number of notifications for a user
     */
    private function createNotifications(string $notifiableType, int $notifiableId, int $count): void
    {
        $types = ['product_approved', 'product_rejected', 'new_product', 'product_edited'];
        $titles = [
            'product_approved' => 'Product Approved',
            'product_rejected' => 'Product Rejected',
            'new_product' => 'New Product Added',
            'product_edited' => 'Product Edited'
        ];
        $messages = [
            'product_approved' => 'Your product has been approved',
            'product_rejected' => 'Your product has been rejected',
            'new_product' => 'A new product has been added and needs approval',
            'product_edited' => 'A product has been edited and needs approval'
        ];

        for ($i = 0; $i < $count; $i++) {
            $type = $types[array_rand($types)];
            $read = (bool) rand(0, 1);

            Notification::create([
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
                'type' => $type,
                'title' => $titles[$type],
                'message' => $messages[$type],
                'data' => ['product_id' => rand(1, 100)],
                'read' => $read,
                'read_at' => $read ? now() : null,
            ]);
        }
    }
}
