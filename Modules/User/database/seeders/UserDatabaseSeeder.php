<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Modules\User\app\Models\User;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        User::factory(100)->create();
    }
}