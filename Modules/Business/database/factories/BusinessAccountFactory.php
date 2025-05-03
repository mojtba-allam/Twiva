<?php

namespace Modules\Business\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Business\app\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'password' => Hash::make('password'), // Default password for testing
            'profile_picture' => fake()->imageUrl(640, 480, 'business'),
            'bio' => fake()->paragraph(),
            'remember_token' => Str::random(10),
        ];
    }
}
