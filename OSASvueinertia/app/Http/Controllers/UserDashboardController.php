<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\OrganizationApplication;
use App\Models\Event;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get the user's applications (most recent first)
        $myApplications = OrganizationApplication::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'title' => $application->organization_name,
                    'status' => $application->status,
                    'updated_at' => $application->updated_at,
                ];
            });
        
        // Get today's event - matching the Admin approach
        $todayEvent = Event::where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
            
        // Get upcoming events - making sure they are truly upcoming
        $upcomingEvents = Event::where('start_date', '>', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();
        
        // Create recent activity
        $recentActivity = $this->getRecentUserActivity($user);
        
        return Inertia::render('Dashboard', [
            'myApplications' => $myApplications,
            'todayEvent' => $todayEvent,
            'upcomingEvents' => $upcomingEvents,
            'recentActivity' => $recentActivity,
        ]);
    }
    
    /**
     * Get recent user activity
     * This would typically come from an Activity model in a real application
     * 
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getRecentUserActivity($user)
    {
        // This is a placeholder. In a real app, you would fetch from an activity log table
        $activity = [];
        
        // Add application activity
        $recentApplications = OrganizationApplication::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        foreach ($recentApplications as $app) {
            $activity[] = [
                'id' => 'app-' . $app->id,
                'type' => 'application',
                'description' => 'You ' . ($app->created_at->eq($app->updated_at) ? 'created' : 'updated') . ' application: ' . $app->organization_name,
                'created_at' => $app->updated_at,
            ];
        }
        
        // Sort by date descending
        usort($activity, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        return array_slice($activity, 0, 5);
    }
}