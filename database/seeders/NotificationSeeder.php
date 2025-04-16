<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Admin;
use App\Models\BusinessAccount;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create notifications for admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            Notification::factory(5)->create([
                'notifiable_type' => Admin::class,
                'notifiable_id' => $admin->id,
            ]);
        }

        // Create notifications for business accounts
        $businessAccounts = BusinessAccount::all();
        foreach ($businessAccounts as $business) {
            Notification::factory(5)->create([
                'notifiable_type' => BusinessAccount::class,
                'notifiable_id' => $business->id,
            ]);
        }

        // Create notifications for users
        $users = User::all();
        foreach ($users as $user) {
            Notification::factory(5)->create([
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);
        }
    }
}
