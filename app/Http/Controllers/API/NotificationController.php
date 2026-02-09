<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends ApiController
{
    /**
     * List current user's notifications with unread count.
     */
    public function index(): JsonResponse
    {
        $notifications = Notification::query()
            ->forUser(auth()->id())
            ->with('sender')
            ->latest()
            ->limit(20)
            ->get();

        $unreadCount = Notification::query()
            ->forUser(auth()->id())
            ->unread()
            ->count();

        return $this->success([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Notification::where('public_id', $id)
            ->forUser(auth()->id())
            ->firstOrFail();

        $notification->markAsRead();

        return $this->success(null, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::query()
            ->forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return $this->success(null, 'All notifications marked as read');
    }
}
