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
     * @return \Inertia\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 10);
        
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

        // More specific check for AJAX requests - only return JSON if explicitly requested with limit parameter
        if (($request->wantsJson() || $request->ajax() || $request->expectsJson()) && $request->has('limit')) {
            $notifications = $query->orderBy('user_notifications.created_at', 'desc')
                ->limit($limit)
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

            return response()->json([
                'notifications' => [
                    'data' => $notifications
                ]
            ]);
        }

        // For regular page requests, return paginated Inertia response
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

        return response()->json([
            'notifications' => [
                'data' => $notifications
            ]
        ]);
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Request $request, $id)
    {
        \DB::table('user_notifications')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        // Check if this is a pure JSON API request (not Inertia)
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }

        // For Inertia requests, redirect back
        return back();
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(Request $request)
    {
        \DB::table('user_notifications')
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        // Check if this is a pure JSON API request (not Inertia)
        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json(['success' => true]);
        }

        // For Inertia requests, redirect back
        return back();
    }
}