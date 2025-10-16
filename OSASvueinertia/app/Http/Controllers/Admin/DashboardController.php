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
                
                // Count only parent organizations (organizations that have sub-organizations)
                $college->parent_orgs_count = $college->users->filter(function($user) {
                    return $user->subOrganizations()->count() > 0;
                })->count();
                
                return $college;
            });
            
        // Get total student organizations count (all users with 'user' role)
        $userRoleId = \App\Models\Role::where('slug', 'user')->value('id');
        $totalStudentOrgs = \App\Models\User::where('role_id', $userRoleId)->count();
        
        // Get total sub-organizations count (users that have a parent organization)
        $totalSubOrgs = \App\Models\User::where('role_id', $userRoleId)
            ->whereNotNull('parent_organization_id')
            ->count();
        
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
        
        // Get approved Plan of Activities count that have ALL their activity reports approved
        $approvedPOAsCount = OrganizationApplication::active()
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-004')
            ->whereHas('activities') // Ensure POA has activities
            ->get()
            ->filter(function($poa) {
                // Get the number of activities for this POA
                $activityCount = $poa->activities->count();
                
                if ($activityCount === 0) {
                    return false; // No activities, so no reports needed
                }
                
                // Required report types for each activity
                $requiredReportTypes = [
                    'LSPU-OSAS-SF-FINANCIAL',
                    'LSPU-OSAS-SF-NARRATIVE', 
                    'LSPU-OSAS-SF-ACCOMPLISHMENT',
                    'LSPU-OSAS-SF-EVAL',
                    'LSPU-OSAS-SF-009'
                ];
                
                // Check each activity page (1 to activityCount)
                for ($pageNumber = 1; $pageNumber <= $activityCount; $pageNumber++) {
                    // For each required report type, check if it exists and is approved
                    foreach ($requiredReportTypes as $reportType) {
                        $report = $poa->activityReports()
                            ->where('activity_page_number', $pageNumber)
                            ->where('report_type', $reportType)
                            ->where('status', 'Approved')
                            ->first();
                            
                        // If any required report is missing or not approved, exclude this POA
                        if (!$report) {
                            return false;
                        }
                    }
                }
                
                // All activities have all required reports approved
                return true;
            })
            ->count();
        
        // Get archive statistics
        $totalArchived = OrganizationApplication::archived()->count();
        $recentlyArchived = OrganizationApplication::archived()
            ->where('archived_at', '>=', now()->subDays(30))
            ->count();
        
        // Get the authenticated user's name
        $userName = auth()->user()->name;

        // Get advisers data: organization name (user name), adviser info from CommitmentForm, and member/officer counts from all approved forms
        $users = \App\Models\User::all();
        $advisersData = $users->map(function($user) {
            // Get ALL approved List of Members forms and sum their member counts
            $allMembersApps = \App\Models\OrganizationApplication::withCount(['members'])
                ->where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('form_type', 'LSPU-OSAS-SF-005')
                ->get();
            
            // Get ALL approved List of Officers forms and sum their officer counts
            $allOfficersApps = \App\Models\OrganizationApplication::withCount(['officers'])
                ->where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('form_type', 'LSPU-OSAS-SF-007')
                ->get();
            
            // Calculate total member count from all approved submissions
            $totalMembersCount = $allMembersApps->sum('members_count');
            
            // Calculate total officer count from all approved submissions
            $totalOfficersCount = $allOfficersApps->sum('officers_count');
            
            // Get the latest approved CommitmentForm for adviser info
            $commitmentForm = \App\Models\OrganizationApplication::where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('form_type', 'LSPU-OSAS-SF-003')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Extract adviser information from CommitmentForm
            $adviser_name = null;
            $adviser_prefix = null;
            $adviser_suffix = null;
            $second_adviser = null;
            $second_adviser_prefix = null;
            $second_adviser_suffix = null;
            
            if ($commitmentForm && $commitmentForm->advisers) {
                $advisersArray = is_string($commitmentForm->advisers) 
                    ? json_decode($commitmentForm->advisers, true) 
                    : $commitmentForm->advisers;
                
                // First page (adviser 1)
                if (isset($advisersArray[0])) {
                    $adviser_name = $advisersArray[0]['adviser_name'] ?? null;
                    $adviser_prefix = $advisersArray[0]['adviser_prefix'] ?? null;
                    $adviser_suffix = $advisersArray[0]['adviser_suffix'] ?? null;
                }
                
                // Second page if exists (adviser 2)
                if (isset($advisersArray[1])) {
                    $second_adviser = $advisersArray[1]['adviser_name'] ?? null;
                    $second_adviser_prefix = $advisersArray[1]['adviser_prefix'] ?? null;
                    $second_adviser_suffix = $advisersArray[1]['adviser_suffix'] ?? null;
                }
            }
            
            // Only show if at least one approved form exists (members, officers, or commitment)
            if ($allMembersApps->isEmpty() && $allOfficersApps->isEmpty() && !$commitmentForm) {
                return null;
            }
            
            // Combine second adviser name with prefix and suffix for display
            $second_adviser_full = null;
            if ($second_adviser) {
                $second_adviser_full = collect([
                    $second_adviser_prefix,
                    $second_adviser,
                    $second_adviser_suffix ? ', ' . $second_adviser_suffix : null
                ])->filter()->implode(' ');
            }
            
            return [
                'organization' => $user->name,
                'adviser_name' => $adviser_name,
                'adviser_prefix' => $adviser_prefix,
                'adviser_suffix' => $adviser_suffix,
                'second_adviser' => $second_adviser_full,
                'members_count' => $totalMembersCount > 0 ? $totalMembersCount : null,
                'officers_count' => $totalOfficersCount > 0 ? $totalOfficersCount : null,
            ];
        })->filter()->values();

        return Inertia::render('Admin/Dashboard', [
            'collegesData' => $colleges,
            'totalStudentOrgs' => $totalStudentOrgs,
            'totalSubOrgs' => $totalSubOrgs,
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