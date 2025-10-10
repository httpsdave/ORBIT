<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationApplication;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function exportPdf(Request $request)
    {
        // Check if user is admin
        $isAdmin = auth()->user()->isAdmin();
        
        // Get Plan of Activities applications
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

        // Apply filters from request
        $filters = $request->input('filters', []);
        $filteredActivities = $this->applyFilters($activities, $filters);

        // Apply sorting from request
        $sort = $request->input('sort', []);
        $sortedActivities = $this->applySorting($filteredActivities, $sort);

        // Generate PDF
        $pdf = Pdf::loadView('pdfs.plan_of_activities_list', [
            'activities' => $sortedActivities,
            'isAdmin' => $isAdmin,
            'generatedDate' => Carbon::now()->format('F d, Y'),
            'generatedBy' => auth()->user()->name,
            'filters' => $filters,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('legal', 'landscape');

        // Return PDF download
        return $pdf->download('plan-of-activities-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    private function applyFilters($activities, $filters)
    {
        $filtered = $activities;

        // Search filter
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $filtered = array_filter($filtered, function($activity) use ($search) {
                return str_contains(strtolower($activity['organization']), $search) ||
                       str_contains(strtolower($activity['objective']), $search) ||
                       str_contains(strtolower($activity['activity_name']), $search) ||
                       str_contains(strtolower($activity['description']), $search) ||
                       str_contains(strtolower($activity['persons_involved']), $search);
            });
        }

        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $filtered = array_filter($filtered, function($activity) use ($filters) {
                return strtolower($activity['status']) === strtolower($filters['status']);
            });
        }

        // Organization filter
        if (!empty($filters['organization'])) {
            $filtered = array_filter($filtered, function($activity) use ($filters) {
                return $activity['organization'] === $filters['organization'];
            });
        }

        // Column filters
        if (!empty($filters['columnFilters'])) {
            foreach ($filters['columnFilters'] as $columnKey => $filter) {
                if (empty($filter['value'])) {
                    continue;
                }

                // Multi-select filter
                if (is_array($filter['value']) && count($filter['value']) > 0) {
                    $filtered = array_filter($filtered, function($activity) use ($columnKey, $filter) {
                        return in_array($activity[$columnKey], $filter['value']);
                    });
                }
                // Standard filter
                elseif (!empty($filter['value'])) {
                    $operator = $filter['operator'] ?? 'contains';
                    $value = $filter['value'];

                    $filtered = array_filter($filtered, function($activity) use ($columnKey, $operator, $value) {
                        $activityValue = $activity[$columnKey] ?? '';
                        
                        if ($operator === 'contains') {
                            return str_contains(strtolower($activityValue), strtolower($value));
                        } elseif ($operator === 'equals') {
                            return strtolower($activityValue) === strtolower($value);
                        }
                        
                        return true;
                    });
                }
            }
        }

        return array_values($filtered);
    }

    private function applySorting($activities, $sort)
    {
        if (empty($sort['column']) || empty($sort['direction'])) {
            return $activities;
        }

        $column = $sort['column'];
        $direction = $sort['direction'];

        usort($activities, function($a, $b) use ($column, $direction) {
            $aVal = $a[$column] ?? '';
            $bVal = $b[$column] ?? '';

            if ($column === 'target_date') {
                $aVal = Carbon::parse($aVal);
                $bVal = Carbon::parse($bVal);
                $result = $aVal->timestamp <=> $bVal->timestamp;
            } elseif ($column === 'budget' || $column === 'target_participants') {
                $result = (float)$aVal <=> (float)$bVal;
            } else {
                $result = strcasecmp($aVal, $bVal);
            }

            return $direction === 'asc' ? $result : -$result;
        });

        return $activities;
    }
}
