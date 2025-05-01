<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\StudentOrg;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get colleges with student organizations count
        $colleges = College::withCount('studentOrgs')->get();
        
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
        
        return Inertia::render('Admin/Dashboard', [
            'collegesData' => $colleges,
            'todayEvent' => $todayEvent,
            'upcomingEvent' => $upcomingEvent,
            'totalStudentOrgs' => $totalStudentOrgs,
        ]);
    }
}