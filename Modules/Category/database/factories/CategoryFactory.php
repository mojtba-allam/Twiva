<?php

namespace Modules\Category\database\factories;

use Modules\Admin\app\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\app\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Category\app\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'admin_id' => Admin::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
