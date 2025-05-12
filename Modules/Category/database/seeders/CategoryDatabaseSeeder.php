<?php

namespace Modules\Category\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\app\Models\Category;
use Modules\Admin\app\Models\Admin;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get admins
        $admins = Admin::all();

        // Create categories using existing admins
        Category::factory(10)->create([
            'admin_id' => fn () => $admins->random()->id
        ]);
    }
}
