<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Admin;
use App\Models\BusinessAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a notification for a specific user
     */
    public function createNotification(
        Model $notifiable,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): Notification {
        return Notification::create([
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Notify all admins
     */
    public function notifyAdmins(string $type, string $title, string $message, array $data = []): void
    {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $this->createNotification($admin, $type, $title, $message, $data);
        }
    }

    /**
     * Notify a business account
     */
    public function notifyBusiness(BusinessAccount $business, string $type, string $title, string $message, array $data = []): void
    {
        $this->createNotification($business, $type, $title, $message, $data);
    }

    /**
     * Notify a user
     */
    public function notifyUser(User $user, string $type, string $title, string $message, array $data = []): void
    {
        $this->createNotification($user, $type, $title, $message, $data);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(Model $notifiable): void
    {
        Notification::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
    }
}
