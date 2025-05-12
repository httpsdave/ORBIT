<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $notifications = Notification::latest()
            ->paginate(10)
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_active' => $notification->is_active,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Notifications/Index', [
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
        $notifications = Notification::latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                // For admin, check if they personally read this notification
                $isRead = \DB::table('user_notifications')
                    ->where('notification_id', $notification->id)
                    ->where('user_id', Auth::id())
                    ->where('is_read', true)
                    ->exists();
                
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $isRead,
                    'created_at' => $notification->created_at->diffForHumans(),
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
        
        // For admins, count notifications they haven't personally read
        $readNotificationIds = \DB::table('user_notifications')
            ->where('user_id', $userId)
            ->where('is_read', true)
            ->pluck('notification_id');
        
        $count = Notification::where('is_active', true)
            ->whereNotIn('id', $readNotificationIds)
            ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read for the admin.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $userId = Auth::id();
        $notificationId = $id;
        
        // Check if a user_notification record exists
        $exists = \DB::table('user_notifications')
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->exists();
        
        if ($exists) {
            // Update existing record
            \DB::table('user_notifications')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->update(['is_read' => true]);
        } else {
            // Create new record
            \DB::table('user_notifications')->insert([
                'user_id' => $userId,
                'notification_id' => $notificationId,
                'is_read' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();
        $notificationIds = Notification::pluck('id');
        
        foreach ($notificationIds as $notificationId) {
            // Check if a user_notification record exists
            $exists = \DB::table('user_notifications')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->exists();
            
            if ($exists) {
                // Update existing record
                \DB::table('user_notifications')
                    ->where('notification_id', $notificationId)
                    ->where('user_id', $userId)
                    ->update(['is_read' => true]);
            } else {
                // Create new record
                \DB::table('user_notifications')->insert([
                    'user_id' => $userId,
                    'notification_id' => $notificationId,
                    'is_read' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Admin/Notifications/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'is_active' => 'boolean',
            'target_audience' => 'required|in:all,specific',
            'user_ids' => 'array|required_if:target_audience,specific',
        ]);

        // Create the notification
        $notification = Notification::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Handle user notification associations
        if ($validated['target_audience'] === 'all') {
            // Create user notifications for all users
            $users = User::all();
            foreach ($users as $user) {
                $this->createUserNotification($user->id, $notification->id);
            }
        } else {
            // Create user notifications for specific users
            foreach ($validated['user_ids'] as $userId) {
                $this->createUserNotification($userId, $notification->id);
            }
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    /**
     * Create a user notification record
     *
     * @param int $userId
     * @param int $notificationId
     * @return void
     */
    private function createUserNotification($userId, $notificationId)
    {
        \DB::table('user_notifications')->insert([
            'user_id' => $userId,
            'notification_id' => $notificationId,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Toggle the active status of a notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_active = !$notification->is_active;
        $notification->save();

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $notification = Notification::findOrFail($id);

        return Inertia::render('Admin/Notifications/Edit', [
            'notification' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'is_active' => $notification->is_active,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'is_active' => 'boolean',
        ]);

        $notification->update($validated);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        
        // First delete all user notification associations
        \DB::table('user_notifications')->where('notification_id', $id)->delete();
        
        // Then delete the notification itself
        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }
    
    
}