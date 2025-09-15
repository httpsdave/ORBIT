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
        // Get colleges with user counts (each user is considered an organization)
        $colleges = College::with('users')
            ->get()
            ->map(function ($college) {
                $college->student_orgs_count = $college->users->count();
                return $college;
            });
            
        // Get total student organizations count (all users with 'user' role)
        $userRoleId = \App\Models\Role::where('slug', 'user')->value('id');
        $totalStudentOrgs = \App\Models\User::where('role_id', $userRoleId)->count();
        
        // Get today's event
        $todayEvent = Event::where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->where(function($query) {
                $query->where('status', '!=', 'cancelled')
                      ->orWhereNull('status');
            })
            ->first();
            
        // Get upcoming event (if no today's event)
        $upcomingEvent = null;
        if (!$todayEvent) {
            $upcomingEvent = Event::where('start_date', '>', Carbon::now())
                ->where(function($query) {
                    $query->where('status', '!=', 'cancelled')
                          ->orWhereNull('status');
                })
                ->orderBy('start_date', 'asc')
                ->first();
        }

        // Get the number of past events (where end_date < now)
        $pastEventsCount = Event::where('end_date', '<', Carbon::now())->count();
        
        // Get pending applications count (only active applications)
        $pendingApplications = OrganizationApplication::active()->where('status', 'pending')->count();
        
        // Get approved Plan of Activities count for this AY (only active applications)
        $approvedPOAsCount = OrganizationApplication::active()
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-004')
            ->count();
        
        // Get archive statistics
        $totalArchived = OrganizationApplication::archived()->count();
        $recentlyArchived = OrganizationApplication::archived()
            ->where('archived_at', '>=', now()->subDays(30))
            ->count();
        
        // Get the authenticated user's name
        $userName = auth()->user()->name;

        // Get advisers data: organization name (user name), adviser_name, second_adviser, and member/officer counts from latest approved forms
        $users = \App\Models\User::all();
        $advisersData = $users->map(function($user) {
            // Latest approved List of Members form
            $latestMembersApp = \App\Models\OrganizationApplication::withCount(['members'])
                ->where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('form_type', 'LSPU-OSAS-SF-005')
                ->orderByDesc('created_at')
                ->first();
            // Latest approved List of Officers form
            $latestOfficersApp = \App\Models\OrganizationApplication::withCount(['officers'])
                ->where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('form_type', 'LSPU-OSAS-SF-007')
                ->orderByDesc('created_at')
                ->first();
            // Use adviser/second adviser from the latest of either form (prefer members, fallback to officers)
            $adviser_name = $latestMembersApp->adviser_name ?? $latestOfficersApp->adviser_name ?? null;
            $second_adviser = $latestMembersApp->second_adviser ?? $latestOfficersApp->second_adviser ?? null;
            // Only show if at least one count exists
            if (!$latestMembersApp && !$latestOfficersApp) {
                return null;
            }
            return [
                'organization' => $user->name,
                'adviser_name' => $adviser_name,
                'second_adviser' => $second_adviser,
                'members_count' => $latestMembersApp->members_count ?? null,
                'officers_count' => $latestOfficersApp->officers_count ?? null,
            ];
        })->filter()->values();

        return Inertia::render('Admin/Dashboard', [
            'collegesData' => $colleges,
            'totalStudentOrgs' => $totalStudentOrgs,
            'todayEvent' => $todayEvent,
            'upcomingEvent' => $upcomingEvent,
            'pendingApplications' => $pendingApplications,
            'userName' => $userName,
            'advisersData' => $advisersData->filter()->values(),
            'pastEventsCount' => $pastEventsCount, // Pass to dashboard
            'approvedPOAsCount' => $approvedPOAsCount, // Pass POA count to dashboard
        ]);
    }
}