<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationApplication;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PlanOfActivitiesController extends Controller
{
    public function index(Request $request)
    {
        // Get all Plan of Activities applications (LSPU-OSAS-SF-004)
        $applications = OrganizationApplication::where('form_type', 'LSPU-OSAS-SF-004')
            ->with(['user', 'activities'])
            ->get();

        // Flatten activities from all applications with organization info
        $activities = [];
        
        foreach ($applications as $application) {
            foreach ($application->activities as $activity) {
                $activities[] = [
                    'id' => $activity->id,
                    'application_id' => $application->id,
                    'organization' => $application->user->name,
                    'objective' => $activity->objective,
                    'activity_name' => $activity->name,
                    'description' => $activity->description,
                    'persons_involved' => $activity->persons_involved,
                    'target_date' => $activity->target_date,
                    'target_date_formatted' => Carbon::parse($activity->target_date)->format('M d, Y'),
                    'budget' => $activity->budget,
                    'target_participants' => $activity->target_participants ?? 'N/A',
                    'status' => $application->status,
                ];
            }
        }

        // Sort activities by target date (nearest first, including past dates)
        usort($activities, function($a, $b) {
            $dateA = Carbon::parse($a['target_date']);
            $dateB = Carbon::parse($b['target_date']);
            $today = Carbon::today();

            // Calculate absolute difference from today
            $diffA = abs($today->diffInDays($dateA, false));
            $diffB = abs($today->diffInDays($dateB, false));

            // If dates are on different sides of today, prioritize the closer one
            if ($diffA != $diffB) {
                return $diffA <=> $diffB;
            }

            // If same distance, prioritize upcoming over past
            return $dateA <=> $dateB;
        });

        return Inertia::render('Admin/PlanOfActivities/Index', [
            'activities' => $activities,
            'totalActivities' => count($activities),
            'isAdmin' => true,
        ]);
    }
}
