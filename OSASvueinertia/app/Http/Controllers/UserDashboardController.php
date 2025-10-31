<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\OrganizationApplication;
use App\Models\Event;
use App\Models\ActivityReport;
use App\Services\ActivityLogService;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

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
        
        // Get recent activity from cache-based service
        $recentActivity = $this->activityLogService->getActivities($user->id, 10);
        
        // Calculate reports to be submitted
        $reportsToBeSubmitted = $this->calculateReportsToBeSubmitted($user->id);
        
        return Inertia::render('Dashboard', [
            'myApplications' => $myApplications,
            'todayEvent' => $todayEvent,
            'upcomingEvents' => $upcomingEvents,
            'recentActivity' => $recentActivity,
            'reportsToBeSubmitted' => $reportsToBeSubmitted,
            'hasSeenTutorial' => $user->has_seen_tutorial ?? false,
        ]);
    }
    
    /**
     * Calculate how many reports need to be submitted for approved Plan of Activities
     */
    private function calculateReportsToBeSubmitted($userId)
    {
        // Get approved Plan of Activities applications for this user
        $approvedPOAs = OrganizationApplication::where('user_id', $userId)
            ->where('status', 'Approved')
            ->where('form_type', 'LSPU-OSAS-SF-004')
            ->whereHas('activities') // Must have activities
            ->with(['activities', 'activityReports'])
            ->get();
        
        $totalReportsNeeded = 0;
        $totalReportsSubmitted = 0;
        
        // Report types that need to be submitted (6 per activity page)
        $requiredReportTypes = [
            'LSPU-OSAS-SF-FINANCIAL',
            'LSPU-OSAS-SF-NARRATIVE', 
            'LSPU-OSAS-SF-ACCOMPLISHMENT',
            'LSPU-OSAS-SF-EVAL',
            'LSPU-OSAS-SF-009',
            'LSPU-OSAS-SF-STATUS-REPORT'
        ];
        
        foreach ($approvedPOAs as $poa) {
            $activityCount = $poa->activities->count();
            
            // Each activity page requires 6 reports
            $totalReportsNeeded += $activityCount * count($requiredReportTypes);
            
            // Count submitted reports for this POA
            $submittedReports = $poa->activityReports()
                ->whereIn('report_type', $requiredReportTypes)
                ->whereNotNull('file_path')
                ->whereIn('status', ['submitted', 'approved'])
                ->count();
            
            $totalReportsSubmitted += $submittedReports;
        }
        
        return max(0, $totalReportsNeeded - $totalReportsSubmitted);
    }
    

}