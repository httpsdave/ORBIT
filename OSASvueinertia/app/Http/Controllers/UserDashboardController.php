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
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
            
        foreach ($recentApplications as $app) {
            $formTypeName = $this->getFormTypeName($app->form_type);
            $isNewSubmission = $app->created_at->eq($app->updated_at);
            
            // Create specific activity descriptions based on action, form type, and status
            if ($isNewSubmission) {
                $description = "You submitted a {$formTypeName}";
                if ($app->organization_name) {
                    $description .= " for {$app->organization_name}";
                }
            } else {
                // For non-new submissions, determine if it's a status change or user update
                // Status changes (Approved/Rejected/etc) are typically done by admin
                // User updates would typically keep status as Pending or change content
                if ($app->status === 'Approved') {
                    $description = "Your {$formTypeName} was approved";
                } elseif ($app->status === 'Rejected' || $app->status === 'Disapproved') {
                    $description = "Your {$formTypeName} was disapproved";
                } elseif ($app->status === 'Pending' && !$isNewSubmission) {
                    // If status is pending and it's not new, likely a user update
                    $description = "You updated your {$formTypeName} submission";
                } else {
                    // Default case for other statuses or unclear situations
                    $description = "Your {$formTypeName} status was updated";
                }
                
                if ($app->organization_name) {
                    $description .= " for {$app->organization_name}";
                }
            }
            
            $activity[] = [
                'id' => 'app-' . $app->id,
                'type' => $this->getActivityType($app->form_type, $isNewSubmission, $app->status),
                'description' => $description,
                'created_at' => $app->updated_at,
                'status' => $app->status,
            ];
        }
        
        // Add other types of activities (placeholder for future expansion)
        // Could include: profile updates, document uploads, meeting attendance, etc.
        
        // Sort by date descending
        usort($activity, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        return array_slice($activity, 0, 5);
    }
    
    /**
     * Get activity type for better iconography and categorization
     * 
     * @param  string  $formType
     * @param  bool  $isNewSubmission
     * @param  string  $status
     * @return string
     */
    private function getActivityType($formType, $isNewSubmission, $status)
    {
        // Return different types based on the nature of the activity
        if ($status === 'Approved') {
            return 'approval';
        } elseif ($status === 'Rejected' || $status === 'Disapproved') {
            return 'rejection';
        } elseif ($isNewSubmission) {
            return 'submission';
        } else {
            return 'update';
        }
    }
    
    /**
     * Get user-friendly form type name
     * 
     * @param  string  $formType
     * @return string
     */
    private function getFormTypeName($formType)
    {
        $formTypeMap = [
            'LSPU-OSAS-SF-001' => 'Organization Recognition Application',
            'LSPU-OSAS-SF-002' => 'Renewal Application',
            'LSPU-OSAS-SF-003' => 'Commitment Form',
            'LSPU-OSAS-SF-004' => 'Plan of Activities',
            'LSPU-OSAS-SF-005' => 'List of Members',
            'LSPU-OSAS-SF-006' => 'Student Certification',
            'LSPU-OSAS-SF-007' => 'List of Officers',
            'LSPU-OSAS-SF-009' => 'Activity Attendance Sheet',
            'LSPU-OSAS-SF-EVAL' => 'Evaluation Form',
            'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'Accomplishment Report',
            'LSPU-OSAS-SF-NARRATIVE' => 'Narrative Report',
            'LSPU-OSAS-SF-BYLAWS' => 'Bylaws Document',
            'LSPU-OSAS-SF-FINANCIAL' => 'Financial Report',
            'LSPU-ACAD-RL' => 'Academic Recognition List',
        ];
        
        return $formTypeMap[$formType] ?? 'Application Form';
    }
}