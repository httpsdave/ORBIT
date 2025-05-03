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
        
        // Get pending applications count
        $pendingApplications = OrganizationApplication::where('status', 'pending')->count();
        
        // Get the authenticated user's name
        $userName = auth()->user()->name;
        
        return Inertia::render('Admin/Dashboard', [
            'collegesData' => $colleges,
            'todayEvent' => $todayEvent,
            'upcomingEvent' => $upcomingEvent,
            'totalStudentOrgs' => $totalStudentOrgs,
            'pendingApplications' => $pendingApplications,
            'userName' => $userName,
        ]);
    }
}