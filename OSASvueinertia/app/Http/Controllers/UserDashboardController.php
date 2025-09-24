<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\OrganizationApplication;
use App\Models\Event;
use App\Models\UserActivity;
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
            ->where(function($query) {
                $query->where('status', '!=', 'cancelled')
                      ->orWhereNull('status');
            })
            ->first();
            
        // Get upcoming events - making sure they are truly upcoming
        $upcomingEvents = Event::where('start_date', '>', Carbon::now())
            ->where(function($query) {
                $query->where('status', '!=', 'cancelled')
                      ->orWhereNull('status');
            })
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();
        
        // Get recent activity from UserActivity model
        $recentActivity = UserActivity::recentForUser($user->id, 10);
        
        return Inertia::render('Dashboard', [
            'myApplications' => $myApplications,
            'todayEvent' => $todayEvent,
            'upcomingEvents' => $upcomingEvents,
            'recentActivity' => $recentActivity,
        ]);
    }
    

}