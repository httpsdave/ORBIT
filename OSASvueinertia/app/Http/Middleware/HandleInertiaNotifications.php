<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaNotifications extends Middleware
{
    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        $auth = $request->user() ? [
            'user' => $request->user()->only('id', 'name', 'email'),
            'unreadNotificationsCount' => function () use ($request) {
                return $request->user()->unreadNotifications()->count();
            },
            'recentNotifications' => function () use ($request) {
                $user = $request->user();
                $notifications = Notification::query()
                    ->forUser($user->id)
                    ->active()
                    ->latest()
                    ->limit(5)
                    ->get();
                    
                // Mark read status from pivot table
                $notificationIds = $notifications->pluck('id')->toArray();
                $readStatus = DB::table('notification_user')
                    ->where('user_id', $user->id)
                    ->whereIn('notification_id', $notificationIds)
                    ->pluck('read_at', 'notification_id')
                    ->toArray();
                    
                // Add read status to each notification
                return $notifications->map(function ($notification) use ($readStatus) {
                    $notification->is_read = isset($readStatus[$notification->id]) && $readStatus[$notification->id] !== null;
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'is_read' => $notification->is_read,
                    ];
                });
            }
        ] : null;

        return array_merge(parent::share($request), [
            'auth' => $auth
        ]);
    }
}