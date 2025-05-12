<?php

namespace Modules\Order\app\Policies;

use Modules\User\app\Models\User;
use Modules\Admin\app\Models\Admin;
use Modules\Order\app\Models\Order;

class OrderPolicy
{
    /**
     * Determine whether the admin can view any orders.
     */

    public function viewAny($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can view the given order.
     */
    public function view($user, Order $order): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $user instanceof User && $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can place new orders.
     */
    public function create($user): bool
    {
        return $user instanceof User;
    }

    /**
     * Determine whether the user can update the given order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can delete the given order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
