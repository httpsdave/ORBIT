<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = \DB::table('user_notifications')
            ->join('notifications', 'user_notifications.notification_id', '=', 'notifications.id')
            ->where('user_notifications.user_id', $userId)
            ->where('notifications.is_active', true)
            ->select(
                'user_notifications.id',
                'notifications.title',
                'notifications.message',
                'notifications.type',
                'user_notifications.is_read',
                'user_notifications.created_at'
            );

        // Apply type filter
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('notifications.type', $request->type);
        }

        // Apply read status filter
        if ($request->has('read') && $request->read !== 'all') {
            $isRead = $request->read === 'read';
            $query->where('user_notifications.is_read', $isRead);
        }

        $notifications = $query->orderBy('user_notifications.created_at', 'desc')
            ->paginate(10)
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => \Carbon\Carbon::parse($notification->created_at)->diffForHumans(),
                ];
            });

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Get recent notifications for the dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecent()
    {
        $userId = Auth::id();
        $notifications = \DB::table('user_notifications')
            ->join('notifications', 'user_notifications.notification_id', '=', 'notifications.id')
            ->where('user_notifications.user_id', $userId)
            ->where('notifications.is_active', true)
            ->select(
                'user_notifications.id',
                'notifications.title',
                'notifications.message',
                'notifications.type',
                'user_notifications.is_read',
                'user_notifications.created_at'
            )
            ->orderBy('user_notifications.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => \Carbon\Carbon::parse($notification->created_at)->diffForHumans(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Get unread notification count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();
        $count = \DB::table('user_notifications')
            ->join('notifications', 'user_notifications.notification_id', '=', 'notifications.id')
            ->where('user_notifications.user_id', $userId)
            ->where('user_notifications.is_read', false)
            ->where('notifications.is_active', true)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        \DB::table('user_notifications')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        \DB::table('user_notifications')
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    
}