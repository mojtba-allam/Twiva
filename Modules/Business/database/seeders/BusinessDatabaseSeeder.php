<?php

namespace Modules\Business\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Business\app\Models\Business;

class BusinessDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create business accounts
        Business::factory(10)->create();
    }
}