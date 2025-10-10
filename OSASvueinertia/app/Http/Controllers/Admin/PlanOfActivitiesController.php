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
        // Check if user is admin
        $isAdmin = auth()->user()->isAdmin();
        
        // Get Plan of Activities applications (LSPU-OSAS-SF-004)
        $query = OrganizationApplication::where('form_type', 'LSPU-OSAS-SF-004')
            ->with(['user', 'activities']);
        
        // If not admin, filter to show only the user's own submissions
        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }
        
        $applications = $query->get();

        // Flatten activities from all applications with organization info
        $activities = [];
        
        foreach ($applications as $application) {
            foreach ($application->activities as $activity) {
                $activities[] = [
                    'id' => $activity->id,
                    'application_id' => $application->id,
                    'organization' => $application->user->name,
                    'objective' => $this->cleanHtmlText($activity->objective),
                    'activity_name' => $this->cleanHtmlText($activity->name),
                    'description' => $this->cleanHtmlText($activity->description),
                    'persons_involved' => $this->cleanHtmlText($activity->persons_involved),
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
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Clean HTML tags and entities from text
     * Converts HTML to plain text while preserving readability
     */
    private function cleanHtmlText($text)
    {
        if (empty($text)) {
            return $text;
        }

        // First, convert common HTML entities to their readable equivalents
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Replace <br>, <br/>, <br />, and </p><p> tags with space or newline as needed
        $text = preg_replace('/<br\s*\/?>/i', ' ', $text);
        $text = preg_replace('/<\/p>\s*<p>/i', ' ', $text);
        
        // Strip all remaining HTML tags
        $text = strip_tags($text);
        
        // Replace multiple spaces with single space
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim whitespace
        $text = trim($text);
        
        return $text;
    }
}
