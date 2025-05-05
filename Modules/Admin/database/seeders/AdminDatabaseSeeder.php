<?php

namespace Modules\Admin\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\app\Models\Admin;

class AdminDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admins
        Admin::factory(10)->create();
    }
}