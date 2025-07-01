<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Event;
use App\Models\StudentOrg;
use App\Models\OrganizationApplication; // Add this for pending applications
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get colleges with student organization counts
        $colleges = College::withCount('studentOrgs')
            ->get();
            
        // Get total student organizations count
        $totalStudentOrgs = StudentOrg::count();
        
        // Get today's event
        $todayEvent = Event::where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
            
        // Get upcoming event (if no today's event)
        $upcomingEvent = null;
        if (!$todayEvent) {
            $upcomingEvent = Event::where('start_date', '>', Carbon::now())
                ->orderBy('start_date', 'asc')
                ->first();
        }
        
        // Get pending applications count (only active applications)
        $pendingApplications = OrganizationApplication::active()->where('status', 'pending')->count();
        
        // Get archive statistics
        $totalArchived = OrganizationApplication::archived()->count();
        $recentlyArchived = OrganizationApplication::archived()
            ->where('archived_at', '>=', now()->subDays(30))
            ->count();
        
        // Get the authenticated user's name
        $userName = auth()->user()->name;

        // Get advisers data: organization name (user name), adviser_name, second_adviser from latest application
        $advisersData = \App\Models\User::whereHas('organizationApplications', function($q) {
                $q->where('status', 'Approved');
            })
            ->with(['latestApplication' => function($q) {
                $q->where('status', 'Approved')
                  ->select(
                      'organization_applications.id',
                      'organization_applications.user_id',
                      'organization_applications.adviser_name',
                      'organization_applications.second_adviser',
                      'organization_applications.status'
                  );
            }])
            ->get()
            ->filter(function($user) {
                return $user->latestApplication !== null;
            })
            ->map(function($user) {
                return [
                    'organization' => $user->name,
                    'adviser_name' => $user->latestApplication->adviser_name ?? null,
                    'second_adviser' => $user->latestApplication->second_adviser ?? null,
                ];
            })
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'collegesData' => $colleges,
            'todayEvent' => $todayEvent,
            'upcomingEvent' => $upcomingEvent,
            'totalStudentOrgs' => $totalStudentOrgs,
            'pendingApplications' => $pendingApplications,
            'totalArchived' => $totalArchived,
            'recentlyArchived' => $recentlyArchived,
            'userName' => $userName,
            'advisersData' => $advisersData,
        ]);
    }
}