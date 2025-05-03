<?php

namespace Modules\Notification\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($notifications->isEmpty()) {
            return response()->json([
                'message' => 'No notifications found',
            ]);
        }

        return response()->json([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $notifications->where('read', false)->count()
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Check if notification exists
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Notification not found'
                ], 404);
            }

            // Check if notification belongs to the user
            if ($notification->notifiable_type !== get_class($user) || $notification->notifiable_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have permission to mark this notification as read'
                ], 403);
            }

            // Check if notification is already read
            if ($notification->read) {
                return response()->json([
                    'message' => 'Notification is already marked as read'
                ], 400);
            }

            $this->notificationService->markAsRead($notification);

            return response()->json([
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while marking the notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Check if notification exists
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Notification not found'
                ], 404);
            }

            // Check if notification belongs to the user
            if ($notification->notifiable_type !== get_class($user) || $notification->notifiable_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have permission to delete this notification'
                ], 403);
            }

            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the notification'
            ], 500);
        }
    }
}
