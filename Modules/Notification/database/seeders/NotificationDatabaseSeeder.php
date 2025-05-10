<?php

namespace Modules\Notification\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Notification\app\Models\Notification;
use Modules\Admin\app\Models\Admin;
use Modules\Business\app\Models\Business;
use Modules\User\app\Models\User;

class NotificationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get users of different types
        $admins = Admin::all();
        $users = User::all();
        $businessAccounts = Business::all();

        // Seed notifications for each type
        $this->seedNotifications($admins, $users, $businessAccounts);
    }

    private function seedNotifications($admins, $users, $businessAccounts): void
    {
        // Create notifications for admins
        foreach ($admins as $admin) {
            $this->createNotifications(Admin::class, $admin->id, 5);
        }

        // Create notifications for business accounts
        foreach ($businessAccounts as $business) {
            $this->createNotifications(Business::class, $business->id, 5);
        }

        // Create notifications for users
        foreach ($users as $user) {
            $this->createNotifications(User::class, $user->id, 5);
        }
    }

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
                'read_at' => $read ? now() : null,
            ]);
        }
    }
}