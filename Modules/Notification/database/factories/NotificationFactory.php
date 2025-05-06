<?php

namespace Modules\Notification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Notification\app\Models\Notification;
use Modules\Admin\app\Models\Admin;
use Modules\Business\app\Models\Business;
use Modules\User\app\Models\User;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $types = ['product_approved', 'product_rejected', 'new_product', 'product_edited'];
        $type = $this->faker->randomElement($types);

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

        return [
            'type' => $type,
            'title' => $titles[$type],
            'message' => $messages[$type],
            'data' => ['product_id' => $this->faker->numberBetween(1, 100)],
            'read' => $this->faker->boolean(),
            'read_at' => $this->faker->boolean() ? now() : null,
        ];
    }
}
