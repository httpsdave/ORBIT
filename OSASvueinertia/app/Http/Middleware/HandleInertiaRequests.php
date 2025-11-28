<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $unreadCount = 0;
        
        if ($user) {
            $unreadCount = DB::table('user_notifications')
                ->join('notifications', 'user_notifications.notification_id', '=', 'notifications.id')
                ->where('user_notifications.user_id', $user->id)
                ->where('user_notifications.is_read', false)
                ->where('notifications.is_active', true)
                ->count();
        }
        
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,  // Make sure this includes the role relationship
                    'profile_photo_url' => $user->profile_photo_url,
                    'description' => $user->description, // Added description field
                    'social_links' => $user->social_links, // Added social_links field
                    'coordinator_name' => SystemSetting::getCoordinatorName(), // Get from system settings
                    'director_name' => SystemSetting::getDirectorName(), // Get from system settings
                    'allow_image_uploads' => SystemSetting::allowImageUploads(), // Get from system settings
                    'allow_link_submissions' => SystemSetting::allowLinkSubmissions(), // Get from system settings
                    'last_name_change_at' => $user->last_name_change_at,
                    'email_verified_at' => $user->email_verified_at,
                    'two_factor_enabled' => $user->two_factor_enabled, // Added 2FA status
                    'two_factor_recovery_codes' => $user->two_factor_recovery_codes, // Added recovery codes for display
                ] : null,
                'unreadNotificationsCount' => $unreadCount,
            ],
            // ... other shared data ...
            'successMessage' => fn () => $request->session()->get('success'),
            'errorMessage' => fn () => $request->session()->get('error'),
            'updateMessage' => fn () => $request->session()->get('updateMessage'),
        ]);
    }
}
