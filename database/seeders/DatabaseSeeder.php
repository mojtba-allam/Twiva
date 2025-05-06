<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\app\Models\Admin;
use Modules\User\app\Models\User;
use Modules\Category\app\Models\Category;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Models\Product;
use Modules\Business\app\Models\Business;
use Modules\Notification\app\Models\Notification;

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
        $this->call(\Modules\User\database\seeders\UserDatabaseSeeder::class);

        // Create business accounts
        $this->call(\Modules\Business\database\seeders\BusinessDatabaseSeeder::class);

        // Create categories using existing admins
        $this->call(\Modules\Category\database\seeders\CategoryDatabaseSeeder::class);

        // Create products using existing admins and categories
        $this->call(\Modules\Product\database\seeders\ProductDatabaseSeeder::class);

        // Create orders without product_id field
        $this->call(\Modules\Order\database\seeders\OrderDatabaseSeeder::class);

        // Create notifications for all users
        $this->call(\Modules\Notification\database\seeders\NotificationDatabaseSeeder::class);
    }

    /**
     * Seed notifications for all user types
     */
    private function seedNotifications($admins, $users, $businessAccounts): void
    {
        // Create notifications for admins
        foreach ($admins as $admin) {
            $this->call(\Modules\Notification\database\seeders\NotificationDatabaseSeeder::class);
            $this->createNotifications(Admin::class, $admin->id, 5);
        }

        // Create notifications for business accounts
        foreach ($businessAccounts as $business) {
            $this->call(\Modules\Notification\database\seeders\NotificationDatabaseSeeder::class);
            $this->createNotifications(Business::class, $business->id, 5);
        }

        // Create notifications for users
        foreach ($users as $user) {
            $this->call(\Modules\Notification\database\seeders\NotificationDatabaseSeeder::class);
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
