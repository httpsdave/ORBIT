<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationApplication;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

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
                    'objective' => $this->cleanHtmlText($activity->objective, 200),
                    'activity_name' => $this->cleanHtmlText($activity->name, 150),
                    'description' => $this->cleanHtmlText($activity->description, 300),
                    'persons_involved' => $this->cleanHtmlText($activity->persons_involved, 150),
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
    private function cleanHtmlText($text, $maxLength = null)
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
        
        // Truncate if maxLength is specified (for PDF optimization)
        if ($maxLength !== null && mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . '...';
        }
        
        return $text;
    }

    public function exportPdf(Request $request)
    {
        try {
            // Increase memory limit and execution time for large PDFs
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
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
                        'organization' => $application->user->name ?? 'N/A',
                        'objective' => $this->cleanHtmlText($activity->objective ?? '', 200),
                        'activity_name' => $this->cleanHtmlText($activity->name ?? '', 150),
                        'description' => $this->cleanHtmlText($activity->description ?? '', 300),
                        'persons_involved' => $this->cleanHtmlText($activity->persons_involved ?? '', 150),
                        'target_date' => $activity->target_date,
                        'target_date_formatted' => $activity->target_date ? Carbon::parse($activity->target_date)->format('M d, Y') : 'N/A',
                        'budget' => $activity->budget ?? 0,
                        'target_participants' => $activity->target_participants ?? 'N/A',
                        'status' => $application->status ?? 'Pending',
                    ];
                }
            }

            // Parse filters from request (support both POST body and GET query params)
            $filters = [];
            
            if ($request->has('filters')) {
                // POST request with filters in body
                $filters = $request->input('filters', []);
            } else {
                // GET request with simplified query params
                $filters = [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'organization' => $request->input('organization'),
                    'columnFilters' => [],
                ];
                
                // Parse simplified column filters (filter_columnName and filter_columnName_op)
                foreach ($request->all() as $key => $value) {
                    if (strpos($key, 'filter_') === 0 && strpos($key, '_op') === false) {
                        $columnKey = str_replace('filter_', '', $key);
                        $operator = $request->input("filter_{$columnKey}_op", 'contains');
                        
                        // Handle comma-separated values for multi-select (operator 'in')
                        if ($operator === 'in' && is_string($value)) {
                            $value = explode(',', $value);
                        }
                        
                        $filters['columnFilters'][$columnKey] = [
                            'operator' => $operator,
                            'value' => $value,
                        ];
                    }
                }
            }
            
            // Apply filters
            $filteredActivities = $this->applyFilters($activities, $filters);

            // Parse sort from request (simplified structure)
            $sort = [];
            if ($request->has('sort')) {
                $sort = $request->input('sort');
            } else {
                // Handle simplified sort parameters
                $sortColumn = $request->input('sort_column');
                $sortDirection = $request->input('sort_direction');
                if ($sortColumn && $sortDirection) {
                    $sort = [
                        'column' => $sortColumn,
                        'direction' => $sortDirection,
                    ];
                }
            }
            $sortedActivities = $this->applySorting($filteredActivities, $sort);

            // Generate PDF
            $pdf = Pdf::loadView('pdfs.plan_of_activities_list', [
                'activities' => $sortedActivities,
                'isAdmin' => $isAdmin,
                'generatedDate' => Carbon::now()->format('F d, Y'),
                'generatedBy' => auth()->user()->name ?? 'Unknown',
                'filters' => $filters,
            ]);

            // Set paper size and orientation
            $pdf->setPaper('legal', 'landscape');
            
            // Set PDF options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'dpi' => 96,
                'defaultFont' => 'sans-serif',
                'enable_php' => false
            ]);

            // Check if action is 'view' to display inline, otherwise download
            $action = $request->input('action', 'download');
            
            if ($action === 'view') {
                // Return PDF for inline viewing in browser
                return $pdf->stream('plan-of-activities-' . Carbon::now()->format('Y-m-d') . '.pdf');
            } else {
                // Return PDF download
                return $pdf->download('plan-of-activities-' . Carbon::now()->format('Y-m-d') . '.pdf');
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('PDF Export Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return user-friendly error response
            if ($request->expectsJson() || $request->input('action') === 'view') {
                return response()->json([
                    'error' => 'Failed to generate PDF',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while generating the PDF. Please try again or contact support.'
                ], 500);
            }
            
            // For non-JSON requests, redirect back with error
            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
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

    public function exportDocx(Request $request)
    {
        try {
            // Increase memory limit and execution time for large documents
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
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
                        'organization' => $application->user->name ?? 'N/A',
                        'objective' => $this->cleanHtmlText($activity->objective ?? '', 200),
                        'activity_name' => $this->cleanHtmlText($activity->name ?? '', 150),
                        'description' => $this->cleanHtmlText($activity->description ?? '', 300),
                        'persons_involved' => $this->cleanHtmlText($activity->persons_involved ?? '', 150),
                        'target_date' => $activity->target_date,
                        'target_date_formatted' => $activity->target_date ? Carbon::parse($activity->target_date)->format('M d, Y') : 'N/A',
                        'budget' => $activity->budget ?? 0,
                        'target_participants' => $activity->target_participants ?? 'N/A',
                        'status' => $application->status ?? 'Pending',
                    ];
                }
            }

            // Parse filters from request
            $filters = [];
            
            if ($request->has('filters')) {
                $filters = $request->input('filters', []);
            } else {
                $filters = [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'organization' => $request->input('organization'),
                    'columnFilters' => [],
                ];
                
                foreach ($request->all() as $key => $value) {
                    if (strpos($key, 'filter_') === 0 && strpos($key, '_op') === false) {
                        $columnKey = str_replace('filter_', '', $key);
                        $operator = $request->input("filter_{$columnKey}_op", 'contains');
                        
                        if ($operator === 'in' && is_string($value)) {
                            $value = explode(',', $value);
                        }
                        
                        $filters['columnFilters'][$columnKey] = [
                            'operator' => $operator,
                            'value' => $value,
                        ];
                    }
                }
            }
            
            // Apply filters
            $filteredActivities = $this->applyFilters($activities, $filters);

            // Parse sort from request
            $sort = [];
            if ($request->has('sort')) {
                $sort = $request->input('sort');
            } else {
                $sortColumn = $request->input('sort_column');
                $sortDirection = $request->input('sort_direction');
                if ($sortColumn && $sortDirection) {
                    $sort = [
                        'column' => $sortColumn,
                        'direction' => $sortDirection,
                    ];
                }
            }
            $sortedActivities = $this->applySorting($filteredActivities, $sort);

            // Check if action is 'view' to display inline, otherwise download
            $action = $request->input('action', 'download');

            // For preview mode, always use HTML version (browsers can't display DOCX inline)
            if ($action === 'view') {
                return $this->generateStyledHtmlAsDoc($sortedActivities, $isAdmin, 'view');
            }

            // For download mode, check if ZIP extension is available for proper DOCX
            $hasZipExtension = class_exists('ZipArchive');
            
            if ($hasZipExtension) {
                // Use PHPWord for proper DOCX (if ZIP extension available)
                return $this->generateDocxWithPhpWord($sortedActivities, $isAdmin, 'download');
            } else {
                // Use styled HTML that looks like the PDF (ZIP extension not available)
                return $this->generateStyledHtmlAsDoc($sortedActivities, $isAdmin, 'download');
            }
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('DOCX Export Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return user-friendly error response
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to generate DOCX',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while generating the DOCX file. Please try again or contact support.'
                ], 500);
            }
            
            // For non-JSON requests, redirect back with error
            return back()->with('error', 'Failed to generate DOCX file. Please try again.');
        }
    }

    private function generateDocxWithPhpWord($activities, $isAdmin, $action = 'download')
    {
        // Original PHPWord implementation for when ZIP extension is available
        $phpWord = new PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator(auth()->user()->name ?? 'OSAS System');
        $properties->setTitle('Plan of Activities');
        $properties->setDescription('Plan of Activities Report');
        $properties->setSubject('Organization Activities');
        
        // Add section with landscape orientation
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
        ]);
        
        // Title
        $section->addText(
            'LAGUNA STATE POLYTECHNIC UNIVERSITY',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'Office of Student Affairs and Services',
            ['name' => 'Arial', 'size' => 11, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'Plan of Activities Report',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );
        
        // Metadata
        $section->addText(
            'Generated: ' . Carbon::now()->format('F d, Y h:i A'),
            ['name' => 'Arial', 'size' => 9],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 100]
        );
        
        $section->addText(
            'Total Activities: ' . count($activities),
            ['name' => 'Arial', 'size' => 9, 'bold' => true],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 200]
        );
        
        // Table styles
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50,
            'alignment' => Jc::CENTER,
            'width' => 100 * 50,
        ];
        
        $headerStyle = ['bold' => true, 'size' => 9, 'color' => 'FFFFFF'];
        $cellStyle = ['size' => 8];
        $headerCellStyle = ['bgColor' => '4472C4', 'valign' => 'center'];
        $cellStyleDef = ['valign' => 'top'];
        
        // Add table
        $table = $section->addTable($tableStyle);
        
        // Header row
        $table->addRow(400);
        if ($isAdmin) {
            $table->addCell(1800, $headerCellStyle)->addText('Organization', $headerStyle, ['alignment' => Jc::CENTER]);
        }
        $table->addCell(1500, $headerCellStyle)->addText('Objective', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(1500, $headerCellStyle)->addText('Activity', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(2000, $headerCellStyle)->addText('Description', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(1300, $headerCellStyle)->addText('Persons Involved', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(1000, $headerCellStyle)->addText('Target Date', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(1000, $headerCellStyle)->addText('Budget', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(800, $headerCellStyle)->addText('Participants', $headerStyle, ['alignment' => Jc::CENTER]);
        $table->addCell(800, $headerCellStyle)->addText('Status', $headerStyle, ['alignment' => Jc::CENTER]);
        
        // Data rows
        foreach ($activities as $activity) {
            $table->addRow();
            
            if ($isAdmin) {
                $table->addCell(1800, $cellStyleDef)->addText($activity['organization'], $cellStyle);
            }
            $table->addCell(1500, $cellStyleDef)->addText($activity['objective'], $cellStyle);
            $table->addCell(1500, $cellStyleDef)->addText($activity['activity_name'], $cellStyle);
            $table->addCell(2000, $cellStyleDef)->addText($activity['description'], $cellStyle);
            $table->addCell(1300, $cellStyleDef)->addText($activity['persons_involved'], $cellStyle);
            $table->addCell(1000, $cellStyleDef)->addText($activity['target_date_formatted'], $cellStyle);
            
            $budget = $activity['budget'];
            $budgetText = $budget == 0 || $budget == 'N/A' ? 'N/A' : '₱' . number_format($budget, 2);
            $table->addCell(1000, $cellStyleDef)->addText($budgetText, $cellStyle);
            
            $table->addCell(800, $cellStyleDef)->addText((string)$activity['target_participants'], $cellStyle);
            
            $statusColor = '000000';
            switch(strtolower($activity['status'])) {
                case 'approved': $statusColor = '28a745'; break;
                case 'pending': $statusColor = 'ffc107'; break;
                case 'disapproved': $statusColor = 'dc3545'; break;
            }
            $table->addCell(800, $cellStyleDef)->addText(
                $activity['status'],
                array_merge($cellStyle, ['color' => $statusColor, 'bold' => true])
            );
        }
        
        // Footer
        $section->addTextBreak(1);
        $section->addText(
            'This report was generated by ' . (auth()->user()->name ?? 'Unknown') . ' on ' . Carbon::now()->format('F d, Y h:i A'),
            ['name' => 'Arial', 'size' => 8, 'italic' => true, 'color' => '666666'],
            ['alignment' => Jc::CENTER]
        );
        
        $filename = 'plan-of-activities-' . Carbon::now()->format('Y-m-d') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword') . '.docx';
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        // Always return as download (view mode uses HTML version)
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function generateStyledHtmlAsDoc($activities, $isAdmin, $action = 'download')
    {
        try {
            $logoPath = public_path('images/lspu-logo.png');
            $namePath = public_path('images/lspu-name.png');
            $logoData = '';
            $nameData = '';
            
            if (file_exists($logoPath)) {
                $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
            
            if (file_exists($namePath)) {
                $nameData = 'data:image/png;base64,' . base64_encode(file_get_contents($namePath));
            }
            
            $filename = 'plan-of-activities-' . Carbon::now()->format('Y-m-d') . '.doc';
            
            if ($action === 'view') {
                // For preview, use the PDF template which is better for browser display
                // We need to replace file paths with base64 data URIs for browser compatibility
                $html = view('pdfs.plan_of_activities_list', [
                    'activities' => $activities,
                    'isAdmin' => $isAdmin,
                    'generatedDate' => Carbon::now()->format('F d, Y'),
                    'generatedBy' => auth()->user()->name ?? 'Unknown',
                    'filters' => [], // No specific filters to display in preview
                ])->render();
                
                // Replace local file paths with base64 data URIs for browser preview
                if ($logoData) {
                    $html = str_replace('src="' . public_path('images/lspu-logo.png') . '"', 'src="' . $logoData . '"', $html);
                    // Also handle the asset() or public_path() variations
                    $html = preg_replace('/src="[^"]*lspu-logo\.png"/', 'src="' . $logoData . '"', $html);
                }
                
                if ($nameData) {
                    $html = str_replace('src="' . public_path('images/lspu-name.png') . '"', 'src="' . $nameData . '"', $html);
                    // Also handle the asset() or public_path() variations
                    $html = preg_replace('/src="[^"]*lspu-name\.png"/', 'src="' . $nameData . '"', $html);
                }
                
                // Return as HTML for viewing in browser
                return response($html, 200, [
                    'Content-Type' => 'text/html; charset=utf-8',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]);
            } else {
                // For download, use the Word-formatted template
                $html = view('pdfs.plan_of_activities_docx', [
                    'activities' => $activities,
                    'isAdmin' => $isAdmin,
                    'generatedDate' => Carbon::now()->format('F d, Y'),
                    'generatedBy' => auth()->user()->name ?? 'Unknown',
                    'logoData' => $logoData,
                ])->render();
                
                // Clean up the HTML to ensure proper encoding for Word format
                $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
                
                // Return as attachment for download
                return response($html, 200, [
                    'Content-Type' => 'application/msword; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0',
                    'Pragma' => 'public',
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('DOCX Export Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate DOCX file: ' . $e->getMessage());
        }
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
