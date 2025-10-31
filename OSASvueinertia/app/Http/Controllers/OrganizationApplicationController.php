<?php

namespace App\Http\Controllers;

use App\Models\OrganizationApplication;
use App\Models\ActivityReport;
use App\Models\Notification;
use App\Models\SystemSetting;
use App\Services\FormDataService;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;


class OrganizationApplicationController extends Controller
{
    use LogsUserActivity;
    public function index(Request $request)
{
    $query = OrganizationApplication::query();
    $perPage = $request->get('per_page', 20);
    
    // Filter by archive status
    if ($request->filled('archive_filter')) {
        if ($request->archive_filter === 'archived') {
            $query->archived();
        } else {
            $query->active(); // Default to active applications
        }
    } else {
        $query->active(); // Default to active applications
    }
    
    // Apply search filter
    if ($request->filled('search')) {
        $search = $request->get('search');
        $query->where(function($q) use ($search) {
            $q->where('form_type', 'like', "%{$search}%")
              ->orWhere('status', 'like', "%{$search}%")
              ->orWhere('organization_name', 'like', "%{$search}%")
              ->orWhereHas('user', function($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%");
              });
        });
    }
    
    // Apply status filter
    if ($request->filled('status_filter')) {
        $statusFilter = $request->get('status_filter');
        
        // Check if it's a "with_signed" filter
        if (str_ends_with(strtolower($statusFilter), '_with_signed')) {
            // Extract the base status (e.g., "pending" from "pending_with_signed")
            $baseStatus = str_replace('_with_signed', '', strtolower($statusFilter));
            
            // Filter by status and only include applications with signed documents
            if ($baseStatus === 'disapproved') {
                $query->whereIn('status', ['rejected', 'disapproved', 'Disapproved', 'Rejected'])
                      ->where(function($q) {
                          $q->whereNotNull('signed_document_path')
                            ->orWhereNotNull('signed_document_link');
                      });
            } else {
                $query->where('status', $baseStatus)
                      ->where(function($q) {
                          $q->whereNotNull('signed_document_path')
                            ->orWhereNotNull('signed_document_link');
                      });
            }
        } else {
            // Regular status filter without signed document requirement
            if (strtolower($statusFilter) === 'disapproved') {
                $query->whereIn('status', ['rejected', 'disapproved', 'Disapproved', 'Rejected']);
            } else {
                // Use exact match for better filtering
                $query->where('status', $statusFilter);
            }
        }
    }
    
    // Apply form type filter
    if ($request->filled('form_type_filter')) {
        $query->where('form_type', $request->get('form_type_filter'));
    }
    
    // Apply organization filter (admin only)
    if ($request->filled('organization_filter')) {
        $query->where('user_id', $request->get('organization_filter'));
    }
    
    // If user is admin, show all applications or filter by user
    if (auth()->user()->isAdmin()) {
        // Apply user filter if provided
        if ($request->filled('user_filter')) {
            $query->where('user_id', $request->user_filter);
        }
        
        $paginatedApplications = $query->with(['user', 'activities'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Get all users who have submitted applications for the filter dropdown
        $users = \App\Models\User::whereHas('organizationApplications')
            ->select('id', 'name'/*, 'student_org_id'*/)
            ->orderBy('name')
            ->get();
            
    } else {
        // For regular users, only show their own applications
        $paginatedApplications = $query->where('user_id', auth()->id())
            ->with(['user', 'activities'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        $users = collect(); // Empty collection for non-admins
    }
    
    // Get filter options from all applications (not just paginated)
    $allApplicationsQuery = OrganizationApplication::query();
    if ($request->filled('archive_filter')) {
        if ($request->archive_filter === 'archived') {
            $allApplicationsQuery->archived();
        } else {
            $allApplicationsQuery->active();
        }
    } else {
        $allApplicationsQuery->active();
    }
    
    if (auth()->user()->isAdmin()) {
        if ($request->filled('user_filter')) {
            $allApplicationsQuery->where('user_id', $request->user_filter);
        }
    } else {
        $allApplicationsQuery->where('user_id', auth()->id());
    }
    
    $allStatuses = $allApplicationsQuery->distinct()->pluck('status')->filter()->values();
    $allFormTypes = $allApplicationsQuery->distinct()->pluck('form_type')->filter()->values();

    return Inertia::render('OrganizationApplications/Index', [
        'applications' => $paginatedApplications->items(),
        'users' => $users,
        'currentUserFilter' => $request->user_filter,
        'currentArchiveFilter' => $request->archive_filter ?? 'active',
        'userId' => auth()->id(),
        'isAdmin' => auth()->user()->isAdmin(),
        'successMessage' => session('success'),
        'errorMessage' => session('error'),
        'currentPage' => $paginatedApplications->currentPage(),
        'hasMorePages' => $paginatedApplications->hasMorePages(),
        'perPage' => $perPage,
        'allStatuses' => $allStatuses,
        'allFormTypes' => $allFormTypes,
    ]);
}
    /**
     * Load more applications for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $query = OrganizationApplication::query();
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        
        // Filter by archive status
        if ($request->filled('archive_filter')) {
            if ($request->archive_filter === 'archived') {
                $query->archived();
            } else {
                $query->active();
            }
        } else {
            $query->active();
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('form_type', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('organization_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply status filter
        if ($request->filled('status_filter')) {
            $statusFilter = $request->get('status_filter');
            
            // Check if it's a "with_signed" filter
            if (str_ends_with(strtolower($statusFilter), '_with_signed')) {
                // Extract the base status (e.g., "pending" from "pending_with_signed")
                $baseStatus = str_replace('_with_signed', '', strtolower($statusFilter));
                
                // Filter by status and only include applications with signed documents
                if ($baseStatus === 'disapproved') {
                    $query->whereIn('status', ['rejected', 'disapproved', 'Disapproved', 'Rejected'])
                          ->where(function($q) {
                              $q->whereNotNull('signed_document_path')
                                ->orWhereNotNull('signed_document_link');
                          });
                } else {
                    $query->where('status', $baseStatus)
                          ->where(function($q) {
                              $q->whereNotNull('signed_document_path')
                                ->orWhereNotNull('signed_document_link');
                          });
                }
            } else {
                // Regular status filter without signed document requirement
                if (strtolower($statusFilter) === 'disapproved') {
                    $query->whereIn('status', ['rejected', 'disapproved', 'Disapproved', 'Rejected']);
                } else {
                    // Use exact match for better filtering
                    $query->where('status', $statusFilter);
                }
            }
        }
        
        // Apply form type filter
        if ($request->filled('form_type_filter')) {
            $query->where('form_type', $request->get('form_type_filter'));
        }
        
        // Apply organization filter (admin only)
        if ($request->filled('organization_filter')) {
            $query->where('user_id', $request->get('organization_filter'));
        }
        
        // Apply user permissions
        if (auth()->user()->isAdmin()) {
            // Apply user filter if provided
            if ($request->filled('user_filter')) {
                $query->where('user_id', $request->user_filter);
            }
        } else {
            // For regular users, only show their own applications
            $query->where('user_id', auth()->id());
        }
        
        $paginatedApplications = $query->with(['user', 'activities'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'applications' => $paginatedApplications->items(),
            'currentPage' => $paginatedApplications->currentPage(),
            'hasMorePages' => $paginatedApplications->hasMorePages(),
            'perPage' => $perPage,
        ]);
    }

    /**
     * Clear saved form data for the authenticated user
     */
    public function clearSavedFormData(Request $request)
    {
        $cleared = \App\Services\FormDataService::clearSavedFormData();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $cleared,
                'message' => $cleared ? 'Saved form data cleared successfully!' : 'No saved data found to clear.'
            ]);
        }

        return redirect()->back()->with(
            $cleared ? 'success' : 'info',
            $cleared ? 'Saved form data cleared successfully!' : 'No saved data found to clear.'
        );
    }

    /**
     * Show form selector page
     */
    public function selectForm()
    {
        return Inertia::render('OrganizationApplications/SelectForm');
    }

    public function create(Request $request)
    {
        // If no form_type provided, redirect to form selector
        if (!$request->has('form_type')) {
            return redirect()->route('applications.select-form');
        }

        // Get saved form data for auto-fill
        $savedFormData = FormDataService::getSavedFormData();
        
        // Ensure coordinator_name and director_name are always set from system defaults
        if (!isset($savedFormData['coordinator_name'])) {
            $savedFormData['coordinator_name'] = SystemSetting::getCoordinatorName();
        }
        if (!isset($savedFormData['director_name'])) {
            $savedFormData['director_name'] = SystemSetting::getDirectorName();
        }
        
        // Set default academic year values if not provided
        if (!isset($savedFormData['academic_year_start']) || empty($savedFormData['academic_year_start'])) {
            $savedFormData['academic_year_start'] = date('y'); // Current year (2-digit)
        }
        if (!isset($savedFormData['academic_year_end']) || empty($savedFormData['academic_year_end'])) {
            $savedFormData['academic_year_end'] = date('y') + 1; // Next year (2-digit)
        }
        
        return Inertia::render('OrganizationApplications/Create', [
            'savedFormData' => $savedFormData,
            'selectedFormType' => $request->input('form_type')
        ]);
    }

    public function store(Request $request)
    {
        // Common fields validation
        $validationRules = [
            'form_type' => 'required|string',
            'organization_name' => 'required|string|max:255',
            // president_name is nullable for specific forms: SF-003, SF-005, SF-006, SF-007, SF-009, SF-EVAL
            'president_name' => in_array($request->form_type, ['LSPU-OSAS-SF-003', 'LSPU-OSAS-SF-005', 'LSPU-OSAS-SF-006', 'LSPU-OSAS-SF-007', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL']) ? 'nullable|string|max:255' : 'required|string|max:255',
            // dean_name is nullable for specific forms: SF-001, SF-002, SF-004, SF-005, SF-006, SF-007, SF-009, SF-EVAL
            'dean_name' => in_array($request->form_type, ['LSPU-OSAS-SF-001', 'LSPU-OSAS-SF-002', 'LSPU-OSAS-SF-004', 'LSPU-OSAS-SF-005', 'LSPU-OSAS-SF-006', 'LSPU-OSAS-SF-007', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL']) ? 'nullable|string|max:255' : 'required|string|max:255',
            'dean_prefix' => 'nullable|string|max:50',
            'dean_suffix' => 'nullable|string|max:50',
            'coordinator_name' => 'required|string|max:255',
            'status' => 'string|in:Pending,Approved,Disapproved',
            'signed_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480', // 20MB limit for signed document
            // application_date is nullable for Plan of Activities (SF-004), Activity Attendance (SF-009), Evaluation (SF-EVAL), and Activity Status Report (uses date_start/activity_date/report_date instead)
            'application_date' => in_array($request->form_type, ['LSPU-OSAS-SF-004', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL', 'LSPU-OSAS-SF-STATUS-REPORT']) ? 'nullable|date' : 'required|date',
        ];
        
        // Add adviser fields for non-commitment forms (except SF-009 which doesn't require adviser)
        if ($request->form_type !== 'LSPU-OSAS-SF-003') {
            $validationRules = array_merge($validationRules, [
                'adviser_name' => $request->form_type === 'LSPU-OSAS-SF-009' ? 'nullable|string|max:255' : 'required|string|max:255',
                'adviser_prefix' => 'nullable|string|max:50',
                'adviser_suffix' => 'nullable|string|max:50',
            ]);
        }
        
        // Add form-specific validation rules
        if ($request->form_type === 'LSPU-OSAS-SF-001') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'director_name' => 'required|string|max:255',
                
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-002') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'college' => 'nullable|string|max:255',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'director_name' => 'required|string|max:255',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-003') {
            // Commitment form specific validation - supports multi-adviser structure
            $validationRules = array_merge($validationRules, [
                'form_date' => 'required|date',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'dean_name' => 'nullable|string|max:255',
                'advisers' => 'required|array|min:1|max:2',
                'advisers.*.adviser_name' => 'required|string|max:255',
                'advisers.*.adviser_prefix' => 'nullable|string|max:50',
                'advisers.*.adviser_suffix' => 'nullable|string|max:50',
                'advisers.*.adviser_signature' => 'nullable|string|max:255',
                'advisers.*.adviser_college' => 'nullable|string|max:255',
                'advisers.*.adviser_rank' => 'nullable|string|max:255',
                'advisers.*.adviser_address' => 'required|string|max:255',
                'advisers.*.adviser_contact' => 'required|string|max:255',
            ]);
        }  elseif ($request->form_type === 'LSPU-OSAS-SF-004') {
            $validationRules = array_merge($validationRules, [
                'secretary_name' => 'required|string|max:255',
                'semester' => 'required|string|in:1st,2nd,Inter',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'director_name' => 'required|string|max:255',
                
                'activities' => 'required|array|min:1',
                'activities.*.objective' => 'required|string|max:255',
                'activities.*.name' => 'required|string|max:255',
                'activities.*.description' => 'required|string|max:1000',
                'activities.*.persons_involved' => 'required|string|max:255',
                'activities.*.target_date' => 'required|date',
                'activities.*.budget' => 'nullable|numeric|min:0|max:999999999.99',
                'activities.*.target_participants' => 'required|integer|min:0|max:99999',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-005') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'semester' => 'required|string|in:1st,2nd,Summer,Inter',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'second_adviser' => 'nullable|string|max:255',
                'second_adviser_prefix' => 'nullable|string|max:10',
                'second_adviser_suffix' => 'nullable|string|max:15',
                'director_name' => 'required|string|max:255',
                'members' => 'required|array|min:1|max:304',
                'members.*.student_name' => 'required|string|max:255',
                'members.*.student_number' => 'required|string|max:50',
                'members.*.course_year_section' => 'required|string|max:255',
                'members.*.photo_path' => 'nullable',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-006') {
            $validationRules = array_merge($validationRules, [
                'certification_date' => 'required|date',
                'students' => 'required|array|min:1',
                'students.*.student_name' => 'required|string|max:255',
                'students.*.course' => 'required|string|max:255',
                'students.*.year_section' => 'required|string|max:255',
                'students.*.course_year_section' => 'required|string|max:255',
                'students.*.position_rank' => 'nullable|string|max:255',
                'students.*.is_bonafide' => 'nullable|boolean',
                'students.*.is_not_academic_probation' => 'nullable|boolean',
                'students.*.is_not_disciplinary_probation' => 'nullable|boolean',
                'students.*.has_position' => 'nullable|boolean',
                'students.*.certification_date' => 'required|date',
                'students.*.dean_name' => 'nullable|string|max:255',
                'students.*.dean_prefix' => 'nullable|string|max:50',
                'students.*.dean_suffix' => 'nullable|string|max:50',
                'students.*.college' => 'nullable|string|max:255',
                'coordinator_name' => 'nullable|string|max:255',
            ]);
            
        }elseif ($request->form_type === 'LSPU-OSAS-SF-007') {
            $validationRules = array_merge($validationRules, [
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'officers' => 'required|array|min:1',
                'officers.*.student_name' => 'required|string|max:255',
                'officers.*.position' => 'required|string|max:255',
                'officers.*.student_number' => 'required|string|max:50',
                'officers.*.photo_path' => 'nullable',
            ]);
        }elseif ($request->form_type === 'LSPU-OSAS-SF-009') {
            $validationRules = array_merge($validationRules, [
                'college' => 'nullable|string|max:255',
                'activity_name' => 'nullable|string|max:255',
                'activity_date' => 'nullable|date',
                'attendees' => 'nullable|array|min:1',
                'attendees.*.name' => 'nullable|string|max:255',
                'attendees.*.course_year_section' => 'nullable|string|max:255',
                'attendees.*.signature' => 'nullable',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-EVAL') {
            // Override common validation rules for evaluation form
            $validationRules['adviser_name'] = 'nullable|string|max:255';
            $validationRules['dean_name'] = 'nullable|string|max:255';
            $validationRules['coordinator_name'] = 'nullable|string|max:255';
            
            // Add evaluation form specific validation
            $validationRules = array_merge($validationRules, [
                'activity_title' => 'required|string|max:255',
                'venue' => 'required|string|max:255',
                'date_start' => 'required|date',
                'date_end' => 'nullable|date|after_or_equal:date_start',
                'time_start' => 'required',
                'time_end' => 'nullable',
                'ratings' => 'required|array|size:15',
                'ratings.*' => ['required', 'regex:/^(?:[1-4]\.[0-9]|5\.0)$/', 'numeric', 'min:1.0', 'max:5.0'],
                'comments_suggestions' => 'nullable|string',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-STATUS-REPORT') {
            // Activity Status Report validation
            $validationRules = array_merge($validationRules, [
                'report_date' => 'required|date',
                'approved_activities' => 'nullable|array',
                'approved_activities.*.title' => 'nullable|string|max:255',
                'approved_activities.*.planned_date' => 'nullable|string|max:255',
                'approved_activities.*.actual_date' => 'nullable|string|max:255',
                'approved_activities.*.proposed_budget' => 'nullable|string|max:255',
                'approved_activities.*.actual_expenditure' => 'nullable|string|max:255',
                'approved_activities.*.target_participants' => 'nullable|string|max:255',
                'approved_activities.*.actual_participants' => 'nullable|string|max:255',
                'approved_activities.*.status' => 'nullable|string|max:255',
                'approved_activities.*.justification' => 'nullable|string|max:1000',
                'unapproved_activities' => 'nullable|array',
                'unapproved_activities.*.title' => 'nullable|string|max:255',
                'unapproved_activities.*.planned_date' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_date' => 'nullable|string|max:255',
                'unapproved_activities.*.proposed_budget' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_expenditure' => 'nullable|string|max:255',
                'unapproved_activities.*.target_participants' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_participants' => 'nullable|string|max:255',
                'unapproved_activities.*.status' => 'nullable|string|max:255',
                'unapproved_activities.*.justification' => 'nullable|string|max:1000',
            ]);
        }
        
        // Additional validation for members count limit
        if ($request->form_type === 'LSPU-OSAS-SF-005' && $request->has('members')) {
            if (count($request->members) > 304) {
                return redirect()->back()->withErrors([
                    'members' => 'Maximum 304 members allowed per submission. Please reduce the number of members or split into multiple submissions.'
                ])->withInput();
            }
        }
        
        
        
        $data = $request->all();
        
        // Explicitly set user_id - make sure this line executes
        $data['user_id'] = auth()->id();
        
        // Set default values for coordinator_name and director_name from system defaults if not provided
        if (empty($data['coordinator_name'])) {
            $data['coordinator_name'] = SystemSetting::getCoordinatorName();
        }
        if (empty($data['director_name'])) {
            $data['director_name'] = SystemSetting::getDirectorName();
        }
        
        // Set default academic year values if not provided
        if (empty($data['academic_year_start'])) {
            $data['academic_year_start'] = date('y'); // Current year (2-digit)
        }
        if (empty($data['academic_year_end'])) {
            $data['academic_year_end'] = date('y') + 1; // Next year (2-digit)
        }
        
        // Defensive: ensure *_report_path fields are null if not set
        foreach ([
            'accomplishment_report_path',
            'narrative_report_path',
            'bylaws_path',
            'financial_report_path',
            'event_letter_path',
        ] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        // For debugging, you might want to log this
        \Log::info('Creating application for user: ' . auth()->id());
        // Set default values for missing fields based on form type
        if ($request->form_type === 'LSPU-OSAS-SF-002') 
        {
            $data['application_date'] = now(); // Use current date for renewal forms
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-003') 
        {
            $data['application_date'] = now(); // Use current date for renewal forms
            
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-004') 
        {
            $data['application_date'] = now(); // Use current date for renewal forms
            
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-006') 
        {
            $data['application_date'] = now(); // Use current date for renewal forms
            
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-005') 
            {
                $data['application_date'] = now(); // Use current date for list of members form
                
                // Process member photos
                if (isset($data['members']) && is_array($data['members'])) {
                    foreach ($data['members'] as $key => $member) {
                        if (isset($member['photo_path']) && $member['photo_path'] instanceof \Illuminate\Http\UploadedFile) {
                            $path = $member['photo_path']->store('member_photos', 'public');
                            $data['members'][$key]['photo_path'] = $path;
                        }
                    }
                }
            }elseif ($request->form_type === 'LSPU-OSAS-SF-007') {
                $data['application_date'] = now(); // Use current date for list of officers form
                
                // Process officer photos
                if (isset($data['officers']) && is_array($data['officers'])) {
                    foreach ($data['officers'] as $key => $officer) {
                        if (isset($officer['photo_path']) && $officer['photo_path'] instanceof \Illuminate\Http\UploadedFile) {
                            $path = $officer['photo_path']->store('officer_photos', 'public');
                            $data['officers'][$key]['photo_path'] = $path;
                        }
                    }
                }
            }elseif ($request->form_type === 'LSPU-OSAS-SF-009') {
                $data['application_date'] = now(); // Use current date for attendance sheet
            } elseif ($request->form_type === 'LSPU-OSAS-SF-EVAL') {
                $data['application_date'] = now(); // Use current date for evaluation form
                // comments_suggestions is already included in $data
            }
        
        // Handle signed document upload
        if ($request->hasFile('signed_document')) {
            $path = $request->file('signed_document')->store('signed_documents', 'public');
            $data['signed_document_path'] = $path;
        }

        // Validate the request data using the defined validation rules
        $validatedData = $request->validate($validationRules);
        
        // Merge validated data with processed data
        $data = array_merge($data, $validatedData);
        
        $application = OrganizationApplication::create($data);

        // Save form data for auto-fill functionality
        FormDataService::saveFormData($data);

        // Save activities if this is the Plan of Activities form
        if ($request->form_type === 'LSPU-OSAS-SF-004' && $request->has('activities')) {
            foreach ($request->activities as $activityData) {
                $application->activities()->create($activityData);
            }
        }

        // Save members if this is the List of Members form
        if ($request->form_type === 'LSPU-OSAS-SF-005' && $request->has('members')) {
            foreach ($data['members'] as $memberData) {
                $application->members()->create($memberData);
            }
        }

        // Save officers if this is the List of Officers form
        if ($request->form_type === 'LSPU-OSAS-SF-007' && $request->has('officers')) {
            foreach ($data['officers'] as $officerData) {
                $application->officers()->create($officerData);
            }
        }

        // Save attendees if this is the Student Activity Attendance Sheet
        if ($request->form_type === 'LSPU-OSAS-SF-009' && $request->has('attendees')) {
            foreach ($data['attendees'] as $attendeeData) {
                $application->attendees()->create($attendeeData);
            }
        }

        // Save student certifications if this is the Student Certification form
        if ($request->form_type === 'LSPU-OSAS-SF-006' && $request->has('students')) {
            foreach ($data['students'] as $studentData) {
                $application->studentCertifications()->create($studentData);
            }
        }

        // Log the application creation activity
        $this->logApplicationCreated($application);

        // Always redirect to the applications index after successful creation for all forms, including renewal
        return redirect()->route('applications.index')->with('success', 'Application submitted successfully!');
    }

    public function edit(OrganizationApplication $application)
    {
        // Check if application is archived - only admins can edit archived applications
        if ($application->is_archived && !auth()->user()->isAdmin()) {
            return redirect()->route('applications.index')->with('error', 'You cannot edit an archived application.');
        }
        
        // Only allow editing if not approved or user is admin
        if (!$application->user || (!auth()->user()->isAdmin() && $application->status === 'Approved')) {
            return redirect()->route('applications.index')->with('error', 'You cannot edit an approved application.');
        }
        
        // Eager load all possible related models for editing
        $application->load('activities', 'members', 'officers', 'attendees', 'studentCertifications');
        
        // Special handling for CommitmentForm to convert single adviser to advisers array
        if ($application->form_type === 'LSPU-OSAS-SF-003') {
            // If advisers data doesn't exist as JSON, create it from single adviser fields
            if (!isset($application->advisers) || empty($application->advisers)) {
                $application->advisers = [[
                    'adviser_name' => $application->adviser_name ?? '',
                    'adviser_prefix' => $application->adviser_prefix ?? '',
                    'adviser_suffix' => $application->adviser_suffix ?? '',
                    'adviser_signature' => $application->adviser_signature ?? '',
                    'adviser_college' => $application->adviser_college ?? '',
                    'adviser_rank' => $application->adviser_rank ?? '',
                    'adviser_address' => $application->adviser_address ?? '',
                    'adviser_contact' => $application->adviser_contact ?? '',
                ]];
            } else {
                // If advisers is stored as JSON string, decode it
                if (is_string($application->advisers)) {
                    $application->advisers = json_decode($application->advisers, true) ?: [];
                }
            }
            
            // Ensure we have at least one adviser with valid data
            if (empty($application->advisers)) {
                $application->advisers = [[
                    'adviser_name' => '',
                    'adviser_prefix' => '',
                    'adviser_suffix' => '',
                    'adviser_signature' => '',
                    'adviser_college' => '',
                    'adviser_rank' => '',
                    'adviser_address' => '',
                    'adviser_contact' => '',
                ]];
            }
        }
        
        // Explicitly handle studentCertifications for StudentCertificationForm
        if ($application->form_type === 'LSPU-OSAS-SF-006') {
            // Convert studentCertifications to students array for frontend compatibility
            $application->students = $application->studentCertifications->map(function($cert) {
                // Split course_year_section into separate course and year_section fields
                $course = '';
                $year_section = '';
                if ($cert->course_year_section) {
                    $parts = explode(', ', $cert->course_year_section, 2);
                    $course = $parts[0] ?? '';
                    $year_section = $parts[1] ?? '';
                }
                
                return [
                    'student_name' => $cert->student_name,
                    'course' => $course,
                    'year_section' => $year_section,
                    'course_year_section' => $cert->course_year_section,
                    'position_rank' => $cert->position_rank,
                    'is_bonafide' => (bool) $cert->is_bonafide,
                    'is_not_academic_probation' => (bool) $cert->is_not_academic_probation,
                    'is_not_disciplinary_probation' => (bool) $cert->is_not_disciplinary_probation,
                    'has_position' => (bool) $cert->has_position,
                    'certification_date' => $cert->certification_date ? $cert->certification_date->format('Y-m-d') : '',
                    'dean_name' => $cert->dean_name ?? '',
                    'dean_prefix' => $cert->dean_prefix ?? '',
                    'dean_suffix' => $cert->dean_suffix ?? '',
                    'college' => $cert->college ?? '',
                ];
            })->toArray();
        }
        
        // Ensure coordinator_name and director_name are populated from system defaults if empty
        if (empty($application->coordinator_name)) {
            $application->coordinator_name = SystemSetting::getCoordinatorName();
        }
        if (empty($application->director_name)) {
            $application->director_name = SystemSetting::getDirectorName();
        }
        
        return Inertia::render('OrganizationApplications/Edit', ['application' => $application]);
    }

    public function update(Request $request, OrganizationApplication $application)
    {
        // Check if application is archived - only admins can update archived applications
        if ($application->is_archived && !auth()->user()->isAdmin()) {
            return redirect()->route('applications.index')->with('error', 'You cannot update an archived application.');
        }
        
        // Only allow updating if not approved or user is admin
        if (!auth()->user()->isAdmin() && $application->status === 'Approved') {
            return redirect()->route('applications.index')->with('error', 'You cannot update an approved application.');
        }

        // Special handling for direct-upload report forms
        $specialReportFormTypes = [
            'LSPU-OSAS-SF-ACCOMPLISHMENT',
            'LSPU-OSAS-SF-NARRATIVE',
            'LSPU-OSAS-SF-BYLAWS',
            'LSPU-OSAS-SF-FINANCIAL',
            'LSPU-ACAD-RL',
        ];
        if (in_array($application->form_type, $specialReportFormTypes)) {
            $columnMap = [
                'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'accomplishment_report_path',
                'LSPU-OSAS-SF-NARRATIVE' => 'narrative_report_path',
                'LSPU-OSAS-SF-BYLAWS' => 'bylaws_path',
                'LSPU-OSAS-SF-FINANCIAL' => 'financial_report_path',
                'LSPU-ACAD-RL' => 'event_letter_path',
            ];
            $column = $columnMap[$application->form_type];
            $validated = $request->validate([
                'file' => 'required|file|mimes:pdf|max:20480', // 20MB limit
            ]);
            // Delete old file if exists
            if ($application->$column) {
                \Storage::disk('public')->delete($application->$column);
            }
            $fileName = $application->form_type . '_' . time() . '.pdf';
            $path = $request->file('file')->storeAs('reports/' . $application->user_id, $fileName, 'public');
            $application->$column = $path;
            $application->save();
            return redirect()->route('applications.index')->with('updateMessage', 'File updated successfully!');
        }
        // Common fields validation
        $validationRules = [
            'organization_name' => 'required|string|max:255',
            // president_name is nullable for specific forms: SF-003, SF-005, SF-006, SF-007, SF-009, SF-EVAL
            'president_name' => in_array($application->form_type, ['LSPU-OSAS-SF-003', 'LSPU-OSAS-SF-005', 'LSPU-OSAS-SF-006', 'LSPU-OSAS-SF-007', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL']) ? 'nullable|string|max:255' : 'required|string|max:255',
            // dean_name is nullable for specific forms: SF-001, SF-002, SF-004, SF-005, SF-006, SF-007, SF-009, SF-EVAL
            'dean_name' => in_array($application->form_type, ['LSPU-OSAS-SF-001', 'LSPU-OSAS-SF-002', 'LSPU-OSAS-SF-004', 'LSPU-OSAS-SF-005', 'LSPU-OSAS-SF-006', 'LSPU-OSAS-SF-007', 'LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL']) ? 'nullable|string|max:255' : 'required|string|max:255',
            'dean_prefix' => 'nullable|string|max:50',
            'dean_suffix' => 'nullable|string|max:50',
            'coordinator_name' => ($application->form_type === 'LSPU-OSAS-SF-006' || $application->form_type === 'LSPU-OSAS-SF-EVAL') ? 'nullable|string|max:255' : 'required|string|max:255',
            'signed_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480', // 20MB limit
        ];
        
        // Add adviser fields for non-commitment forms (except SF-009 and SF-EVAL which don't require adviser)
        if ($application->form_type !== 'LSPU-OSAS-SF-003') {
            $validationRules = array_merge($validationRules, [
                'adviser_name' => in_array($application->form_type, ['LSPU-OSAS-SF-009', 'LSPU-OSAS-SF-EVAL']) ? 'nullable|string|max:255' : 'required|string|max:255',
                'adviser_prefix' => 'nullable|string|max:50',
                'adviser_suffix' => 'nullable|string|max:50',
            ]);
        }
        
        // Add form-specific validation rules based on form type
        if ($application->form_type === 'LSPU-OSAS-SF-001') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'director_name' => 'required|string|max:255',
            ]);
        } elseif ($application->form_type === 'LSPU-OSAS-SF-002') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'college' => 'nullable|string|max:255',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'director_name' => 'required|string|max:255',
            ]);
        } elseif ($application->form_type === 'LSPU-OSAS-SF-003') {
            $validationRules = array_merge($validationRules, [
                'form_date' => 'required|date',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'dean_name' => 'nullable|string|max:255',
                'director_name' => 'required|string|max:255',
                'application_date' => 'nullable|date',
                'advisers' => 'required|array|min:1|max:2',
                'advisers.*.adviser_name' => 'required|string|max:255',
                'advisers.*.adviser_prefix' => 'nullable|string|max:50',
                'advisers.*.adviser_suffix' => 'nullable|string|max:50',
                'advisers.*.adviser_signature' => 'nullable|string|max:255',
                'advisers.*.adviser_college' => 'nullable|string|max:255',
                'advisers.*.adviser_rank' => 'nullable|string|max:255',
                'advisers.*.adviser_address' => 'required|string|max:255',
                'advisers.*.adviser_contact' => 'required|string|max:255',
            ]);
        } elseif ($application->form_type === 'LSPU-OSAS-SF-004') {
            $validationRules = array_merge($validationRules, [
                'secretary_name' => 'required|string|max:255',
                'semester' => 'required|string|in:1st,2nd,Inter',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'director_name' => 'required|string|max:255',
            ]);
            
            // Special handling for activities below
            if ($request->has('activities')) {
                $validationRules = array_merge($validationRules, [
                    'activities' => 'required|array|min:1',
                    'activities.*.objective' => 'required|string|max:255',
                    'activities.*.name' => 'required|string|max:255',
                    'activities.*.description' => 'required|string|max:1000',
                    'activities.*.persons_involved' => 'required|string|max:255',
                    'activities.*.target_date' => 'required|date',
                    'activities.*.budget' => 'nullable|numeric|min:0|max:999999999.99',
                    'activities.*.target_participants' => 'required|integer|min:0|max:99999',
                ]);
            }
        } elseif ($application->form_type === 'LSPU-OSAS-SF-005') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'semester' => 'required|string|in:1st,2nd,Summer,Inter',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'second_adviser' => 'nullable|string|max:255',
                'second_adviser_prefix' => 'nullable|string|max:10',
                'second_adviser_suffix' => 'nullable|string|max:15',
                'director_name' => 'required|string|max:255',
                'members' => 'required|array|min:1|max:304',
                'members.*.student_name' => 'required|string|max:255',
                'members.*.student_number' => 'required|string|max:50',
                'members.*.course_year_section' => 'required|string|max:255',
                'members.*.photo_path' => 'nullable',
            ]);
            
            // Special handling for members below
        } elseif ($application->form_type === 'LSPU-OSAS-SF-006') {
            $validationRules = array_merge($validationRules, [
                'certification_date' => 'required|date',
                'students' => 'required|array|min:1',
                'students.*.student_name' => 'required|string|max:255',
                'students.*.course' => 'required|string|max:255',
                'students.*.year_section' => 'required|string|max:255',
                'students.*.course_year_section' => 'required|string|max:255',
                'students.*.position_rank' => 'nullable|string|max:255',
                'students.*.is_bonafide' => 'nullable|boolean',
                'students.*.is_not_academic_probation' => 'nullable|boolean',
                'students.*.is_not_disciplinary_probation' => 'nullable|boolean',
                'students.*.has_position' => 'nullable|boolean',
                'students.*.certification_date' => 'required|date',
                'students.*.dean_name' => 'nullable|string|max:255',
                'students.*.dean_prefix' => 'nullable|string|max:50',
                'students.*.dean_suffix' => 'nullable|string|max:50',
                'students.*.college' => 'nullable|string|max:255',
            ]);
        } elseif ($application->form_type === 'LSPU-OSAS-SF-007') {
            $validationRules = array_merge($validationRules, [
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
            ]);
            
            // Special handling for officers below
        } elseif ($application->form_type === 'LSPU-OSAS-SF-009') {
            $validationRules = array_merge($validationRules, [
                'college' => 'nullable|string|max:255',
                'activity_name' => 'nullable|string|max:255',
                'activity_date' => 'nullable|date',
            ]);
            
            // Special handling for attendees below
        } elseif ($application->form_type === 'LSPU-OSAS-SF-EVAL') {
            // Validation for evaluation form
            $validationRules = array_merge($validationRules, [
                'activity_title' => 'required|string|max:255',
                'venue' => 'required|string|max:255',
                'date_start' => 'required|date',
                'date_end' => 'nullable|date|after_or_equal:date_start',
                'time_start' => 'required',
                'time_end' => 'nullable',
                'ratings' => 'required|array|size:15',
                'ratings.*' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        // Ensure it's a string and matches the format
                        if (!is_string($value)) {
                            $fail('The rating must be a string.');
                            return;
                        }
                        
                        // Check format using regex
                        if (!preg_match('/^(?:[1-4]\.[0-9]|5\.0)$/', $value)) {
                            $fail('The rating must be in format X.Y where X is 1-4 and Y is 0-9, or exactly 5.0');
                            return;
                        }
                        
                        // Check numeric range
                        $numValue = floatval($value);
                        if ($numValue < 1.0 || $numValue > 5.0) {
                            $fail('The rating must be between 1.0 and 5.0.');
                        }
                    }
                ],
                'comments_suggestions' => 'nullable|string',
            ]);
        } elseif ($application->form_type === 'LSPU-OSAS-SF-STATUS-REPORT') {
            // Activity Status Report validation
            $validationRules = array_merge($validationRules, [
                'report_date' => 'required|date',
                'approved_activities' => 'nullable|array',
                'approved_activities.*.title' => 'nullable|string|max:255',
                'approved_activities.*.planned_date' => 'nullable|string|max:255',
                'approved_activities.*.actual_date' => 'nullable|string|max:255',
                'approved_activities.*.proposed_budget' => 'nullable|string|max:255',
                'approved_activities.*.actual_expenditure' => 'nullable|string|max:255',
                'approved_activities.*.target_participants' => 'nullable|string|max:255',
                'approved_activities.*.actual_participants' => 'nullable|string|max:255',
                'approved_activities.*.status' => 'nullable|string|max:255',
                'approved_activities.*.justification' => 'nullable|string|max:1000',
                'unapproved_activities' => 'nullable|array',
                'unapproved_activities.*.title' => 'nullable|string|max:255',
                'unapproved_activities.*.planned_date' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_date' => 'nullable|string|max:255',
                'unapproved_activities.*.proposed_budget' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_expenditure' => 'nullable|string|max:255',
                'unapproved_activities.*.target_participants' => 'nullable|string|max:255',
                'unapproved_activities.*.actual_participants' => 'nullable|string|max:255',
                'unapproved_activities.*.status' => 'nullable|string|max:255',
                'unapproved_activities.*.justification' => 'nullable|string|max:1000',
            ]);
        }
        
        // Additional validation for members count limit
        if ($application->form_type === 'LSPU-OSAS-SF-005' && $request->has('members')) {
            if (count($request->members) > 304) {
                return redirect()->back()->withErrors([
                    'members' => 'Maximum 304 members allowed per submission. Please reduce the number of members or split into multiple submissions.'
                ])->withInput();
            }
        }
        
        // Debug logging for CommitmentForm updates
        if ($application->form_type === 'LSPU-OSAS-SF-003') {
            \Log::info('CommitmentForm Update Debug', [
                'application_id' => $application->id,
                'request_data' => $request->all(),
                'validation_rules' => $validationRules,
                'form_type' => $application->form_type
            ]);
        }

        // Validate the request data
        try {
            $validatedData = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($application->form_type === 'LSPU-OSAS-SF-003') {
                \Log::error('CommitmentForm Validation Failed', [
                    'application_id' => $application->id,
                    'errors' => $e->errors(),
                    'request_data' => $request->all()
                ]);
            }
            throw $e;
        }
        
        // Defensive: ensure *_report_path fields are null if not set
        foreach ([
            'accomplishment_report_path',
            'narrative_report_path',
            'bylaws_path',
            'financial_report_path',
            'event_letter_path',
        ] as $field) {
            if (!isset($validatedData[$field]) || $validatedData[$field] === '') {
                $validatedData[$field] = null;
            }
        }
        
        // Handle signed document upload on update
        if ($request->hasFile('signed_document')) {
            // Delete old document if exists
            if ($application->signed_document_path) {
                Storage::disk('public')->delete($application->signed_document_path);
            }
            
            $path = $request->file('signed_document')->store('signed_documents', 'public');
            $validatedData['signed_document_path'] = $path;
        }
        
        // Set default values for coordinator_name and director_name from system defaults if not provided
        if (empty($validatedData['coordinator_name'])) {
            $validatedData['coordinator_name'] = SystemSetting::getCoordinatorName();
        }
        if (empty($validatedData['director_name'])) {
            $validatedData['director_name'] = SystemSetting::getDirectorName();
        }
        
        // Set default academic year values if not provided
        if (empty($validatedData['academic_year_start'])) {
            $validatedData['academic_year_start'] = date('y'); // Current year (2-digit)
        }
        if (empty($validatedData['academic_year_end'])) {
            $validatedData['academic_year_end'] = date('y') + 1; // Next year (2-digit)
        }
        
        // Update the application with validated data
        $application->update($validatedData);
        
        // Debug logging for CommitmentForm updates
        if ($application->form_type === 'LSPU-OSAS-SF-003') {
            \Log::info('CommitmentForm After Update', [
                'application_id' => $application->id,
                'validated_data' => $validatedData,
                'updated_application' => $application->fresh()->toArray()
            ]);
        }
        
        // Save form data for auto-fill functionality
        FormDataService::saveFormData($validatedData);
        
        // Handle form-specific related data updates
        if ($application->form_type === 'LSPU-OSAS-SF-003' && $request->has('advisers')) {
            // For commitment form, the advisers data is already saved as part of the main update
            // No additional relationship handling needed as advisers is stored as JSON
        }
        
        if ($application->form_type === 'LSPU-OSAS-SF-004' && $request->has('activities')) {
            // Delete existing activities
            $application->activities()->delete();
            
            // Create new activities
            foreach ($request->activities as $activityData) {
                $application->activities()->create($activityData);
            }
        }
        
        if ($application->form_type === 'LSPU-OSAS-SF-005' && $request->has('members')) {
            // Delete existing members
            $application->members()->delete();
            
            // Create new members
            foreach ($request->members as $memberData) {
                // Handle member photo if present
                if (isset($memberData['photo_path']) && $memberData['photo_path'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $memberData['photo_path']->store('member_photos', 'public');
                    $memberData['photo_path'] = $path;
                }
                
                $application->members()->create($memberData);
            }
        }
        
        if ($application->form_type === 'LSPU-OSAS-SF-007' && $request->has('officers')) {
            // Delete existing officers
            $application->officers()->delete();
            
            // Create new officers
            foreach ($request->officers as $officerData) {
                // Handle officer photo if present
                if (isset($officerData['photo_path']) && $officerData['photo_path'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $officerData['photo_path']->store('officer_photos', 'public');
                    $officerData['photo_path'] = $path;
                }
                
                $application->officers()->create($officerData);
            }
        }
        
        if ($application->form_type === 'LSPU-OSAS-SF-009' && $request->has('attendees')) {
            // Delete existing attendees
            $application->attendees()->delete();
            
            // Create new attendees
            foreach ($request->attendees as $attendeeData) {
                $application->attendees()->create($attendeeData);
            }
        }

        if ($application->form_type === 'LSPU-OSAS-SF-006' && $request->has('students')) {
            // Delete existing student certifications
            $application->studentCertifications()->delete();
            
            // Create new student certifications
            foreach ($request->students as $studentData) {
                $application->studentCertifications()->create($studentData);
            }
        }
        
        // Log the application update activity
        $this->logApplicationUpdated($application);
        
        return redirect()->route('applications.index')->with('updateMessage', 'Application updated successfully!');
    }

    public function destroy(OrganizationApplication $application)
    {
        // Only allow deleting if not approved or user is admin
        if (!auth()->user()->isAdmin() && $application->status === 'Approved') {
            return redirect()->route('applications.index')->with('error', 'You cannot delete an approved application.');
        }
        // Log the application deletion activity before deleting
        $this->logApplicationDeleted($application);
        
        // Delete the signed document if it exists
        if ($application->signed_document_path) {
            Storage::disk('public')->delete($application->signed_document_path);
        }
        
        $application->delete();
        return redirect()->route('applications.index');
    }
    
    /**
     * Log when a user views an application (for activity tracking)
     */
    public function logView(OrganizationApplication $application)
    {
        // Only allow users to log views of their own applications
        if ($application->user_id === auth()->id()) {
            $this->logApplicationViewed($application);
        }
        
        return response()->json(['success' => true]);
    }
    
    // Add a new method to handle signed document uploads separately
    public function uploadSignedDocument(Request $request, OrganizationApplication $application)
    {
        // Only allow upload if not approved or user is admin
        if (!auth()->user()->isAdmin() && $application->status === 'Approved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot upload a signed document after approval.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot upload a signed document after approval.');
        }
        
        // Only allow PDF - 20MB limit
        $request->validate([
            'signed_document' => 'required|file|mimes:pdf|max:20480' // 20MB limit
        ], [
            'signed_document.max' => 'The file you\'re attempting to upload is over the limit (20MB). Please compress your file and try again.',
            'signed_document.mimes' => 'Only PDF files are allowed.',
            'signed_document.required' => 'Please select a file to upload.'
        ]);
        
        // Delete old document if exists
        if ($application->signed_document_path) {
            Storage::disk('public')->delete($application->signed_document_path);
        }
        
        $path = $request->file('signed_document')->store('signed_documents', 'public');
        $application->signed_document_path = $path;
        $application->save();
        
        // Log document upload activity
        $fileName = $request->file('signed_document')->getClientOriginalName();
        $this->logDocumentUploaded($application, $fileName);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Signed document uploaded successfully']);
        }
        
        return redirect()->back()->with('success', 'Signed document uploaded successfully');
    }

    public function submitLink(Request $request, OrganizationApplication $application)
    {
        // Only allow submission if not approved or user is admin
        if (!auth()->user()->isAdmin() && $application->status === 'Approved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot submit a document link after approval.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot submit a document link after approval.');
        }
        
        // Validate the link
        $request->validate([
            'signed_document_link' => [
                'required',
                'url',
                'regex:/^https:\/\/(drive\.google\.com|docs\.google\.com)\/.+/'
            ]
        ], [
            'signed_document_link.regex' => 'Must be a valid Google Drive or Google Docs link.'
        ]);
        
        // Clear old document path if exists
        if ($application->signed_document_path) {
            Storage::disk('public')->delete($application->signed_document_path);
            $application->signed_document_path = null;
        }
        
        // Save the link
        $application->signed_document_link = $request->signed_document_link;
        $application->save();
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Document link submitted successfully']);
        }
        
        return redirect()->back()->with('success', 'Document link submitted successfully');
    }

    public function deleteSignedDocument(Request $request, OrganizationApplication $application)
    {
        // Only allow delete if not approved or user is admin
        if (!auth()->user()->isAdmin() && $application->status === 'Approved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot delete the signed document after approval.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot delete the signed document after approval.');
        }
        
        // Check if document exists (file or link)
        if ($application->signed_document_path || $application->signed_document_link) {
            // Get file name for logging before deletion
            $fileName = 'signed_document.pdf'; // Default name
            if ($application->signed_document_path) {
                $fileName = basename($application->signed_document_path);
            }
            
            // Delete file from storage if exists
            if ($application->signed_document_path) {
                Storage::disk('public')->delete($application->signed_document_path);
            }
            
            // Log document deletion activity
            $this->logDocumentDeleted($application, $fileName);
            
            // Update database record
            $application->signed_document_path = null;
            $application->signed_document_link = null;
            $application->save();
            
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Signed document deleted successfully']);
            }
            
            return redirect()->back()->with('success', 'Signed document deleted successfully');
        }
        
        if ($request->expectsJson()) {
            return response()->json(['error' => 'No signed document found'], 404);
        }
        
        return redirect()->back()->with('error', 'No signed document found');
    }
    
    // Add a method to view/download the signed document
    public function viewSignedDocument(OrganizationApplication $application)
    {
        if ($application->signed_document_link) {
            // Redirect to the link for external documents
            return redirect($application->signed_document_link);
        }
        
        if (!$application->signed_document_path) {
            return redirect()->back()->with('error', 'No signed document available');
        }
        
        $filePath = Storage::disk('public')->path($application->signed_document_path);
        return response()->file($filePath);
    }

    /**
     * Show the SPA document view page
     */
    public function showDocumentView(OrganizationApplication $application)
    {
        // Check if user has permission to view this application
        if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this application.');
        }

        // Get view type from query parameter (signed or unsigned)
        $viewType = request()->query('view', 'signed');

        // If viewing unsigned, no need to check for signed document
        if ($viewType === 'signed') {
            // Check if application has a signed document (file or link)
            if (!$application->signed_document_path && !$application->signed_document_link) {
                return redirect()->back()->with('error', 'No signed document found for this application.');
            }

            // If it's a link, redirect to the external document
            if ($application->signed_document_link) {
                return redirect($application->signed_document_link);
            }
        }

        // Log application view activity (only for non-admin users viewing their own applications)
        if (!auth()->user()->isAdmin() && $application->user_id === auth()->id()) {
            $this->logApplicationViewed($application);
        }

        // Determine back URL based on user role
        $backUrl = auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return Inertia::render('DocumentView', [
            'application' => $application->load('user'),
            'backUrl' => $backUrl,
            'isAdmin' => auth()->user()->isAdmin(),
            'viewType' => $viewType,
        ]);
    }

    /**
     * Show the feedback view page
     */
    public function showFeedbackView(OrganizationApplication $application)
    {
        // Check if user has permission to view this application
        if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this application.');
        }

        // Determine back URL based on user role
        $backUrl = auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return Inertia::render('FeedbackView', [
            'application' => $application->load('user'),
            'backUrl' => $backUrl,
            'isAdmin' => auth()->user()->isAdmin(),
        ]);
    }

    /**
     * Update application status (API endpoint for SPA)
     */
    public function updateStatus(Request $request, OrganizationApplication $application)
{
    

    // Ensure only admins can update status
    if (!auth()->user()->isAdmin()) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized. Only administrators can update application status.'], 403);
        }
        return redirect()->route('home')->with('error', 'Unauthorized. Only administrators can update application status.');
    }

    // Change validation to match your frontend exactly
    $validated = $request->validate([
        'status' => 'required|string|in:Pending,Approved,Disapproved',  // Note: Capital letters to match your frontend
        'feedback' => 'nullable|string|max:1000',
    ]);

    // Store the old status and feedback to check what changed
    $oldStatus = $application->status;
    $oldFeedback = $application->feedback;
    
    $application->status = $validated['status'];  // Use validated data
    
    // Set feedback with default message for Approved status if no custom feedback provided
    if (!empty($validated['feedback'])) {
        $application->feedback = $validated['feedback'];
    } elseif (strtolower($validated['status']) === 'approved') {
        $application->feedback = 'Goodjob! thank you for your submission. Keep it up.';
    } else {
        $application->feedback = null;
    }
    
    $application->reviewed_by = auth()->id();
    $application->reviewed_at = now();
    
    $application->save();

    // Create notifications based on what changed
    $statusChanged = $oldStatus !== $validated['status'];
    $feedbackChanged = $application->feedback !== $oldFeedback;
    
    if ($statusChanged) {
        // Status changed - create status change notification (includes feedback if provided)
        $this->createStatusChangeNotification($application, $validated['status'], $application->feedback);
    } elseif ($feedbackChanged) {
        // Only feedback changed - create feedback notification
        $this->createFeedbackNotification($application, $application->feedback);
    }

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'application' => $application->fresh()
        ]);
    }

    return redirect()->back()->with('success', 'Application status updated successfully');
}

    /**
     * Update the status of an activity report
     */
    public function updateReportStatus(Request $request, OrganizationApplication $application, ActivityReport $report)
    {
        // Ensure only admins can update status
        if (!auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Only administrators can update report status.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized. Only administrators can update report status.');
        }

        // Validate the request
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Approved,Disapproved',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Update the report with default feedback for Approved status if no custom feedback provided
        $report->status = $validated['status'];
        if (!empty($validated['feedback'])) {
            $report->feedback = $validated['feedback'];
        } elseif (strtolower($validated['status']) === 'approved') {
            $report->feedback = 'Goodjob! thank you for your submission. Keep it up.';
        } else {
            $report->feedback = null;
        }
        $report->reviewed_by = auth()->id();
        $report->reviewed_at = now();
        $report->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
                'report' => $report->fresh()
            ]);
        }

        // For Inertia requests, return the same page with updated data
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Report status updated successfully',
                'report' => $report->fresh(),
            ]);
        }

        // Redirect back to reports page with success message
        return redirect()->route('applications.reports', $application)
            ->with('success', 'Report status updated successfully');
    }

    /**
     * Save feedback for an application (API endpoint for SPA)
     */
    public function saveFeedback(Request $request, OrganizationApplication $application)
    {
        // Check if user has permission to provide feedback
        if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized to provide feedback for this application.'], 403);
            }
            abort(403, 'Unauthorized to provide feedback for this application.');
        }

        $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        // Store old feedback to check if it changed
        $oldFeedback = $application->feedback;
        
        $application->feedback = $request->feedback;
        $application->save();

        // Create notification for new feedback (if it's different from old feedback)
        if ($oldFeedback !== $request->feedback && auth()->user()->isAdmin()) {
            $this->createFeedbackNotification($application, $request->feedback);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback saved successfully',
                'application' => $application->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Feedback saved successfully');
    }



    
    public function exportPdf(OrganizationApplication $application, Request $request)
    {
        $pdf = Pdf::loadView('pdfs.organization_application', compact('application'))
                ->setPaper('A4', 'portrait');

        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Application_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Application_' . $application->organization_name . '.pdf');
    }

    public function exportRenewalPdf(OrganizationApplication $application, Request $request)
    {
        $pdf = Pdf::loadView('pdfs.organization_renewal', compact('application'))
                ->setPaper('A4', 'portrait');
                
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Renewal_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Renewal_' . $application->organization_name . '.pdf');
    }

    public function exportCommitmentPdf(OrganizationApplication $application, Request $request)
    {
        $pdf = Pdf::loadView('pdfs.organization_commitment', compact('application'))
                ->setPaper('A4', 'portrait');
                
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Commitment_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Commitment_' . $application->organization_name . '.pdf');
    }

    public function exportPlanPdf(OrganizationApplication $application, Request $request)
    {
        try {
            $application->load('activities'); // Eager load activities for the PDF

            // Check if activities exist
            if ($application->activities->isEmpty()) {
                return redirect()->back()->with('error', 'No activities found for this organization.');
            }

            // Use the combined template for all cases
            $pdf = Pdf::loadView('pdfs.organization_plan_combined', [
                'application' => $application,
                'activities' => $application->activities
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'chroot' => base_path(),
                'isPhpEnabled' => true
            ]);
                    
            $action = $request->query('action', 'download');
            
            if ($action === 'view') {
                return $pdf->stream('Plan_' . $application->organization_name . '.pdf');
            }
            
            return $pdf->download('Plan_' . $application->organization_name . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again or contact support.');
        }
    }

    public function exportMembersPdf(OrganizationApplication $application, Request $request)
    {
        // Eager load the members relationship
        $application->load('members');

        // Check if members data exists
        if ($application->members->isEmpty()) {
            return redirect()->back()->with('error', 'No members found for this organization.');

        }

        // Generate the PDF using the loaded data
        $pdf = Pdf::loadView('pdfs.organization_list', [
                'application' => $application, 
                'members' => $application->members
            ])
            ->setPaper('A4', 'portrait');
            
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Members_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Members_' . $application->organization_name . '.pdf');
    }

    public function exportCertificationPdf(OrganizationApplication $application, Request $request)
    {
        // Eager load the student certifications relationship
        $application->load('studentCertifications');

        // Check if student certifications data exists
        if ($application->studentCertifications->isEmpty()) {
            return redirect()->back()->with('error', 'No student certifications found for this organization.');
        }

        // Generate the PDF using the loaded data
        $pdf = Pdf::loadView('pdfs.organization_certification', [
                'application' => $application, 
                'studentCertifications' => $application->studentCertifications
            ])
            ->setPaper('A4', 'portrait');
            
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Certification_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Certification_' . $application->organization_name . '.pdf');
    }

    public function exportOfficersPdf(OrganizationApplication $application, Request $request)
    {
        // Eager load the officers relationship
        $application->load('officers');

        // Check if officers data exists
        if ($application->officers->isEmpty()) {
            return redirect()->back()->with('error', 'No officers found for this organization.');

        }

        // Generate the PDF using the loaded data
        $pdf = Pdf::loadView('pdfs.organization_officers', [
                'application' => $application, 
                'officers' => $application->officers
            ])
            ->setPaper('A4', 'portrait');
            
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Officers_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Officers_' . $application->organization_name . '.pdf');
    }

    public function exportAttendancePdf(OrganizationApplication $application, Request $request)
    {
        // Eager load the attendees relationship
        $application->load('attendees');

        // Check if attendees data exists
        if ($application->attendees->isEmpty()) {
            return redirect()->back()->with('error', 'No attendees found for this activity.');

        }

        // Generate the PDF using the loaded data
        $pdf = Pdf::loadView('pdfs.organization_attendance', [
                'application' => $application, 
                'attendees' => $application->attendees
            ])
            ->setPaper('A4', 'portrait');
            
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Attendance_' . $application->activity_name . '.pdf');
        }
        
        return $pdf->download('Attendance_' . $application->activity_name . '.pdf');
    }

    public function exportEvaluationPdf(OrganizationApplication $application, Request $request)
    {
        $pdf = Pdf::loadView('pdfs.organization_evaluation', compact('application'))
                ->setPaper('A4', 'portrait');
        
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Evaluation_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Evaluation_' . $application->organization_name . '.pdf');
    }

    public function exportStatusReportPdf(OrganizationApplication $application, Request $request)
    {
        $pdf = Pdf::loadView('pdfs.organization_statusreport', compact('application'))
                ->setPaper('A4', 'landscape');
                
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Status_Report_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Status_Report_' . $application->organization_name . '.pdf');
    }

    /**
     * Preview a form template with sample data as PDF
     */
    public function previewForm($form_type, Request $request)
    {
        // Map form_type to blade template and sample data
        $templateMap = [
            'LSPU-OSAS-SF-001' => 'pdfs.organization_application',
            'LSPU-OSAS-SF-002' => 'pdfs.organization_renewal',
            'LSPU-OSAS-SF-003' => 'pdfs.organization_commitment',
            'LSPU-OSAS-SF-004' => 'pdfs.organization_plan',
            'LSPU-OSAS-SF-005' => 'pdfs.organization_list',
            'LSPU-OSAS-SF-006' => 'pdfs.organization_certification',
            'LSPU-OSAS-SF-007' => 'pdfs.organization_officers',
            'LSPU-OSAS-SF-009' => 'pdfs.organization_attendance',
            'LSPU-OSAS-SF-EVAL' => 'pdfs.organization_evaluation',
            'LSPU-OSAS-SF-EVALSHEET' => 'pdfs.organization_evalsheet',
            'LSPU-OSAS-SF-STATUS' => 'pdfs.organization_statusreport',
        ];

        if (!isset($templateMap[$form_type])) {
            abort(404, 'Form type not found');
        }

        // Sample data for each form type
        $sampleData = [
            'LSPU-OSAS-SF-001' => [
                'application' => (object)[
                    'organization_name' => '',
                    'president_name' => '',
                    'adviser_name' => '',
                    'dean_name' => '',
                    'coordinator_name' => '',
                    'director_name' => '',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-001',
                ],
            ],
            'LSPU-OSAS-SF-002' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'director_name' => 'Sample Data',
                    'college' => 'Sample Data',
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-002',
                ],
            ],
            'LSPU-OSAS-SF-003' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'adviser_college' => 'Sample Data',
                    'adviser_rank' => 'Sample Data',
                    'adviser_address' => 'Sample Data',
                    'adviser_contact' => '0917-0322',
                    'form_date' => now(),
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'coordinator_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'director_name' => 'Sample Data',
                    'form_type' => 'LSPU-OSAS-SF-003',
                ],
            ],
            'LSPU-OSAS-SF-004' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'secretary_name' => 'Sample Data',
                    'director_name' => 'Sample Data',
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-004',
                ],
                'activities' => [
                    (object)[
                        'objective' => 'Sample Data',
                        'name' => 'Sample Data',
                        'description' => 'Sample Data',
                        'persons_involved' => 'Sample Data',
                        'target_date' => now()->addMonth(),
                        'budget' => 12345.67,
                    ],
                ],
            ],
            'LSPU-OSAS-SF-005' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'semester' => '1st',
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-005',
                ],
                'members' => [
                    (object)[
                        'student_name' => 'Sample Data',
                        'student_number' => '0322-1234',
                        'course_year_section' => 'Sample Data',
                        'photo_path' => null,
                    ],
                    (object)[
                        'student_name' => 'Sample Data',
                        'student_number' => '0322-5678',
                        'course_year_section' => 'Sample Data',
                        'photo_path' => null,
                    ],
                ],
            ],
            'LSPU-OSAS-SF-006' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-006',
                ],
                'studentCertifications' => [
                    (object)[
                        'student_name' => 'Sample Data',
                        'course_year_section' => 'Sample Data',
                        'position_rank' => 'Sample Data',
                        'is_bonafide' => true,
                        'is_not_academic_probation' => true,
                        'is_not_disciplinary_probation' => true,
                        'has_position' => false,
                        'certification_date' => now()->format('Y-m-d'),
                    ],
                    (object)[
                        'student_name' => 'Sample Data',
                        'course_year_section' => 'Sample Data',
                        'position_rank' => 'Sample Data',
                        'is_bonafide' => true,
                        'is_not_academic_probation' => false,
                        'is_not_disciplinary_probation' => true,
                        'has_position' => true,
                        'certification_date' => now()->format('Y-m-d'),
                    ],
                ],
            ],
            'LSPU-OSAS-SF-007' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-007',
                ],
                'officers' => [
                    (object)[
                        'student_name' => 'Sample Data',
                        'position' => 'Sample Data',
                        'student_number' => '0322-1111',
                        'photo_path' => null,
                    ],
                    (object)[
                        'student_name' => 'Sample Data',
                        'position' => 'Sample Data',
                        'student_number' => '0322-2222',
                        'photo_path' => null,
                    ],
                ],
            ],
            'LSPU-OSAS-SF-009' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'activity_name' => '',
                    'activity_date' => '',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'college' => '',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-009',
                ],
                'attendees' => [
                    (object)[
                        'name' => '',
                        'course_year_section' => '',
                        'signature' => null,
                    ],
                    (object)[
                        'name' => '',
                        'course_year_section' => '',
                        'signature' => null,
                    ],
                ],
            ],
            'LSPU-OSAS-SF-EVAL' => [
                'application' => (object)[
                    'organization_name' => 'Sample Data',
                    'activity_title' => 'Sample Data',
                    'venue' => 'Sample Data',
                    'date_start' => now(),
                    'date_end' => now()->addDay(),
                    'time_start' => '08:00',
                    'time_end' => '12:00',
                    'president_name' => 'Sample Data',
                    'adviser_name' => 'Sample Data',
                    'dean_name' => 'Sample Data',
                    'coordinator_name' => 'Sample Data',
                    'application_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-EVAL',
                    'ratings' => ['5.0','4.5','4.0','3.5','3.0','2.5','2.0','1.5','1.0','4.2','3.8','2.7','4.9','3.3','2.1'],
                    'comments_suggestions' => 'Sample Data',
                ],
            ],
            'LSPU-OSAS-SF-EVALSHEET' => [
                'application' => (object)[
                    'form_type' => 'LSPU-OSAS-SF-EVALSHEET',
                ],
            ],
            'LSPU-OSAS-SF-STATUS' => [
                'application' => (object)[
                    'organization_name' => '',
                    'president_name' => '',
                    'adviser_name' => '',
                    'dean_name' => '',
                    'coordinator_name' => '',
                    'director_name' => '',
                    'academic_year_start' => '24',
                    'academic_year_end' => '25',
                    'report_date' => now(),
                    'form_type' => 'LSPU-OSAS-SF-STATUS',
                    'approved_activities' => [],
                    'unapproved_activities' => [],
                ],
                'activities' => [
                    [
                        'title' => 'Clean up Drive',
                        'planned_date_from' => '2024-08-16',
                        'planned_date_to' => '2024-08-16',
                        'actual_date_from' => '2024-08-16',
                        'actual_date_to' => '2024-08-16',
                        'proposed_budget' => '0',
                        'actual_expenditure' => '0',
                        'target_male' => '40',
                        'target_female' => '40',
                        'actual_male' => '40',
                        'actual_female' => '40',
                        'status' => 'Completed',
                        'justification' => '',
                    ],
                    [
                        'title' => 'Freshmen Orientation',
                        'planned_date_from' => '2024-08-28',
                        'planned_date_to' => '2024-08-28',
                        'actual_date_from' => '2024-08-28',
                        'actual_date_to' => '2024-08-28',
                        'proposed_budget' => '5000',
                        'actual_expenditure' => '3154',
                        'target_male' => '171',
                        'target_female' => '171',
                        'actual_male' => '197',
                        'actual_female' => '197',
                        'status' => 'Completed',
                        'justification' => '',
                    ],
                    [
                        'title' => 'Teacher\'s Day Appreciation',
                        'planned_date_from' => '2024-10-01',
                        'planned_date_to' => '2024-10-01',
                        'actual_date_from' => '',
                        'actual_date_to' => '',
                        'proposed_budget' => '12000',
                        'actual_expenditure' => '',
                        'target_male' => '',
                        'target_female' => '',
                        'actual_male' => '',
                        'actual_female' => '',
                        'status' => 'Cancelled',
                        'justification' => 'The activity was not conducted due to scheduling conflicts, as faculty members were occupied with BSCS accreditation preparations.',
                    ],
                    [
                        'title' => 'Mobile Legend Campus Clash',
                        'planned_date_from' => '2025-01-01',
                        'planned_date_to' => '2025-01-01',
                        'actual_date_from' => '2025-05-20',
                        'actual_date_to' => '2025-05-20',
                        'proposed_budget' => '0',
                        'actual_expenditure' => '0',
                        'target_male' => '60',
                        'target_female' => '60',
                        'actual_male' => '60',
                        'actual_female' => '60',
                        'status' => 'Completed Late',
                        'justification' => 'The activity was completed later than scheduled as it was integrated into the CCS Fest.',
                    ],
                ],
            ],
        ];

        $template = $templateMap[$form_type];
        $data = $sampleData[$form_type];

        // Replace sample coordinator_name and director_name with actual system defaults if available
        if (isset($data['application'])) {
            $coordinatorName = SystemSetting::getCoordinatorName();
            if ($coordinatorName) {
                $data['application']->coordinator_name = $coordinatorName;
            }
            $directorName = SystemSetting::getDirectorName();
            if ($directorName) {
                $data['application']->director_name = $directorName;
            }
        }

        // Fix: wrap members, officers, studentCertifications in collections, and set attendees as property of application
        if ($form_type === 'LSPU-OSAS-SF-005') {
            $data['members'] = collect($data['members'] ?? []);
        }
        if ($form_type === 'LSPU-OSAS-SF-007') {
            $data['officers'] = collect($data['officers'] ?? []);
        }
        if ($form_type === 'LSPU-OSAS-SF-006') {
            $data['studentCertifications'] = collect($data['studentCertifications'] ?? []);
        }
        if ($form_type === 'LSPU-OSAS-SF-009') {
            // Attendance expects $application->attendees as a collection of arrays
            if (isset($data['attendees'])) {
                $data['application']->attendees = collect($data['attendees'])->map(function($a) { return (array)$a; });
            }
        }

        $orientation = ($form_type === 'LSPU-OSAS-SF-STATUS') ? 'landscape' : 'portrait';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($template, $data)
            ->setPaper('A4', $orientation);

        $action = $request->query('action', 'view');
        $filename = 'Preview_' . $form_type . '.pdf';

        if ($action === 'view') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    /**
     * Auto-save form data for the authenticated user
     */
    public function autoSaveFormData(Request $request)
    {
        $request->validate([
            'form_data' => 'required|array',
        ]);

        // Save form data for auto-fill functionality
        FormDataService::saveFormData($request->form_data);

        // Return a 204 No Content response for silent auto-save
        return response()->noContent();
    }

    public function uploadReport(Request $request)
    {
        $request->validate([
            'form_type' => 'required|string|in:LSPU-OSAS-SF-ACCOMPLISHMENT,LSPU-OSAS-SF-NARRATIVE,LSPU-OSAS-SF-BYLAWS,LSPU-OSAS-SF-FINANCIAL,LSPU-ACAD-RL', // Added LSPU-ACAD-RL
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB limit
        ], [
            'file.max' => 'The file you\'re attempting to upload is over the limit (20MB). Please compress your file and try again.',
            'file.mimes' => 'Only PDF files are allowed.',
            'file.required' => 'Please select a file to upload.',
            'form_type.required' => 'Form type is required.',
            'form_type.in' => 'Invalid form type selected.'
        ]);

        $user = $request->user();
        $formType = $request->input('form_type');
        $file = $request->file('file');

        $columnMap = [
            'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'accomplishment_report_path',
            'LSPU-OSAS-SF-NARRATIVE' => 'narrative_report_path',
            'LSPU-OSAS-SF-BYLAWS' => 'bylaws_path',
            'LSPU-OSAS-SF-FINANCIAL' => 'financial_report_path',
            'LSPU-ACAD-RL' => 'event_letter_path', // NEW
        ];
        $column = $columnMap[$formType];

        // Create a new application for this upload
        $application = OrganizationApplication::create([
            'user_id' => $user->id,
            'form_type' => $formType,
            'organization_name' => $user->name,
            'president_name' => $user->name,
            'status' => 'Pending',
            'application_date' => now(), // Fix: set required field
        ]);

        $fileName = $formType . '_' . time() . '.pdf';
        $path = $file->storeAs('reports/' . $user->id, $fileName, 'public');
        $application->$column = $path;
        $application->save();

        // Use Inertia-friendly redirect for SPA
        if ($request->header('X-Inertia')) {
            return redirect()->route('applications.index')->with('success', 'Application submitted successfully!');
        }
        return redirect()->route('applications.index')->with('success', 'Application submitted successfully!');
    }

    /**
     * Create automatic notification when application status changes
     */
    private function createStatusChangeNotification($application, $newStatus, $feedback = null)
    {
        // Get form type name for the notification
        $formTypeNames = [
            'LSPU-OSAS-SF-001' => 'Organization Recognition',
            'LSPU-OSAS-SF-002' => 'Renewal Application',
            'LSPU-OSAS-SF-003' => 'Commitment Form',
            'LSPU-OSAS-SF-004' => 'Plan of Activities',
            'LSPU-OSAS-SF-005' => 'Members List',
            'LSPU-OSAS-SF-006' => 'Certification Form',
            'LSPU-OSAS-SF-007' => 'Officers List',
            'LSPU-OSAS-SF-009' => 'Student Activity Attendance Sheet',
            'LSPU-OSAS-SF-EVAL' => 'Evaluation Summary',
            'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'Accomplishment Report',
            'LSPU-OSAS-SF-NARRATIVE' => 'Narrative Report',
            'LSPU-OSAS-SF-BYLAWS' => 'Constitution & By-Laws',
            'LSPU-OSAS-SF-FINANCIAL' => 'Financial Report',
            'LSPU-ACAD-RL' => 'Event Letter',
        ];

        $formTypeName = $formTypeNames[$application->form_type] ?? $application->form_type;

        // Determine notification type and message based on status
        switch (strtolower($newStatus)) {
            case 'approved':
                $notificationType = 'success';
                $title = 'Application Approved';
                $message = "Great news! Your {$formTypeName} has been approved!";
                if ($feedback) {
                    $message .= "\n\nFeedback: {$feedback}";
                }
                break;

            case 'disapproved':
                $notificationType = 'info';
                $title = 'Application Disapproved';
                $message = "Your {$formTypeName} has been disapproved.";
                if ($feedback) {
                    $message .= "\n\nFeedback: {$feedback}";
                } else {
                    $message .= " Please check your application details and contact us if you have any questions.";
                }
                break;

            case 'pending':
                $notificationType = 'info';
                $title = 'Application Status Update';
                $message = "Your {$formTypeName} status has been updated to pending review.";
                if ($feedback) {
                    $message .= "\n\nFeedback: {$feedback}";
                }
                break;

            default:
                return; // Don't create notification for unknown status
        }

        // Create the notification
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $notificationType,
            'is_active' => true,
        ]);

        // Attach the notification to the specific user
        $notification->users()->attach($application->user_id, [
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create notification specifically for feedback updates
     */
    private function createFeedbackNotification($application, $feedback)
    {
        // Get form type name for the notification
        $formTypeNames = [
            'LSPU-OSAS-SF-001' => 'Organization Recognition',
            'LSPU-OSAS-SF-002' => 'Renewal Application',
            'LSPU-OSAS-SF-003' => 'Commitment Form',
            'LSPU-OSAS-SF-004' => 'Plan of Activities',
            'LSPU-OSAS-SF-005' => 'Members List',
            'LSPU-OSAS-SF-006' => 'Certification Form',
            'LSPU-OSAS-SF-007' => 'Officers List',
            'LSPU-OSAS-SF-009' => 'Student Activity Attendance Sheet',
            'LSPU-OSAS-SF-EVAL' => 'Evaluation Summary',
            'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'Accomplishment Report',
            'LSPU-OSAS-SF-NARRATIVE' => 'Narrative Report',
            'LSPU-OSAS-SF-BYLAWS' => 'Constitution & By-Laws',
            'LSPU-OSAS-SF-FINANCIAL' => 'Financial Report',
            'LSPU-ACAD-RL' => 'Event Letter',
        ];

        $formTypeName = $formTypeNames[$application->form_type] ?? $application->form_type;

        // Create feedback notification
        $title = 'New Feedback Received';
        $message = "You have received new feedback for your {$formTypeName}.";
        $message .= "\n\nFeedback: {$feedback}";

        // Create the notification
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'is_active' => true,
        ]);

        // Attach the notification to the specific user
        $notification->users()->attach($application->user_id, [
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Show the reports management page for Plan of Activities
     */
    public function reports(OrganizationApplication $application)
    {
        // Ensure this is a Plan of Activities form
        if ($application->form_type !== 'LSPU-OSAS-SF-004') {
            abort(404, 'Reports are only available for Plan of Activities submissions.');
        }

        // Ensure user owns this application or is admin
        if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this application.');
        }

        // Get the number of activity pages (1 activity per page)
        $activityCount = $application->activities()->count();
        
        // Load existing reports
        $reports = $application->activityReports()
            ->orderBy('activity_page_number')
            ->orderBy('report_type')
            ->get();

        // Group reports by activity page and report type for easy access
        $reportsByPageAndType = $reports->groupBy('activity_page_number')
            ->map(function ($pageReports) {
                return $pageReports->keyBy('report_type');
            });

        return Inertia::render('Applications/Reports', [
            'application' => $application->load('activities'),
            'activityCount' => $activityCount,
            'reports' => $reports,
            'reportsByPageAndType' => $reportsByPageAndType,
            'reportTypes' => [
                'LSPU-OSAS-SF-FINANCIAL' => 'Financial Report',
                'LSPU-OSAS-SF-NARRATIVE' => 'Narrative Report',
                'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'Accomplishment Report',
                'LSPU-OSAS-SF-EVAL' => 'Evaluation Summary',
                'LSPU-OSAS-SF-009' => 'Activity Attendance Sheet',
                'LSPU-OSAS-SF-STATUS-REPORT' => 'Status Report',
            ],
            'isAdmin' => auth()->user()->isAdmin()
        ]);
    }

    /**
     * Store a new report for an activity
     */
    public function storeReport(Request $request, OrganizationApplication $application)
    {
        try {
            \Log::info('Report upload started', [
                'application_id' => $application->id,
                'user_id' => auth()->id(),
                'form_type' => $application->form_type,
                'request_data' => $request->except(['report_file'])
            ]);

            // Ensure this is a Plan of Activities form
            if ($application->form_type !== 'LSPU-OSAS-SF-004') {
                abort(404, 'Reports are only available for Plan of Activities submissions.');
            }

            // Ensure user owns this application or is admin
            if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this application.');
            }

            $request->validate([
                'activity_page_number' => 'required|integer|min:1',
                'report_type' => 'required|in:LSPU-OSAS-SF-FINANCIAL,LSPU-OSAS-SF-NARRATIVE,LSPU-OSAS-SF-ACCOMPLISHMENT,LSPU-OSAS-SF-EVAL,LSPU-OSAS-SF-009,LSPU-OSAS-SF-STATUS-REPORT',
                'report_file' => 'required|file|mimes:pdf|max:20480', // 20MB max, PDF only
            ]);

            \Log::info('Validation passed', [
                'application_id' => $application->id,
                'activity_page_number' => $request->activity_page_number,
                'report_type' => $request->report_type
            ]);

            // Check if report already exists for this activity page and type
            $existingReport = $application->activityReports()
                ->where('activity_page_number', $request->activity_page_number)
                ->where('report_type', $request->report_type)
                ->first();

            if ($existingReport) {
                // Delete old file if it exists - use public disk for persistence
                if ($existingReport->file_path && Storage::disk('public')->exists($existingReport->file_path)) {
                    Storage::disk('public')->delete($existingReport->file_path);
                }
            }

            // Store the uploaded file - use public disk for Railway volume persistence
            $file = $request->file('report_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('activity_reports', $filename, 'public');

            \Log::info('File stored successfully', [
                'application_id' => $application->id,
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName()
            ]);

            // Create or update the report
            $report = $application->activityReports()->updateOrCreate(
                [
                    'activity_page_number' => $request->activity_page_number,
                    'report_type' => $request->report_type,
                ],
                [
                    'file_path' => $filePath,
                    'original_filename' => $file->getClientOriginalName(),
                    'status' => 'Pending', // Use new enum value
                    'submitted_at' => now(),
                ]
            );

            \Log::info('Report record created/updated', [
                'application_id' => $application->id,
                'report_id' => $report->id
            ]);

            // Redirect back to reports page with success message
            return redirect()->route('applications.reports', $application)
                ->with('success', 'Report uploaded successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Report upload failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to upload report. Please try again.');
        }
    }

    /**
     * Download or view a report file
     */
    public function downloadReport(OrganizationApplication $application, $reportId, Request $request)
    {
        try {
            // Ensure this is a Plan of Activities form
            if ($application->form_type !== 'LSPU-OSAS-SF-004') {
                abort(404, 'Reports are only available for Plan of Activities submissions.');
            }

            // Ensure user owns this application or is admin
            if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this application.');
            }

            $report = $application->activityReports()->findOrFail($reportId);

            // Check if file exists - use public disk for persistence
            if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
                abort(404, 'File not found.');
            }

            // If action=view, return file for viewing in browser
            if ($request->query('action') === 'view') {
                $filePath = Storage::disk('public')->path($report->file_path);
                return response()->file($filePath);
            }

            return Storage::disk('public')->download($report->file_path, $report->original_filename);
            
        } catch (\Exception $e) {
            \Log::error('Report download failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'report_id' => $reportId,
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);
            
            abort(404, 'File not found or could not be accessed.');
        }
    }

    /**
     * Update a report (replace the file)
     */
    public function updateReport(Request $request, OrganizationApplication $application, $reportId)
    {
        try {
            // Ensure this is a Plan of Activities form
            if ($application->form_type !== 'LSPU-OSAS-SF-004') {
                abort(404, 'Reports are only available for Plan of Activities submissions.');
            }

            // Ensure user owns this application or is admin
            if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this application.');
            }

            $report = $application->activityReports()->findOrFail($reportId);

            // Check if report is approved - prevent updates to approved reports
            if (strtolower($report->status) === 'approved') {
                return redirect()->back()
                    ->with('error', 'Cannot update an approved report.');
            }

            // Validate the uploaded file
            $request->validate([
                'report_file' => 'required|file|mimes:pdf|max:20480', // 20MB in KB
            ]);

            // Delete the old file from storage if it exists - use public disk for persistence
            if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }

            // Store the new file - use public disk for Railway volume persistence
            $file = $request->file('report_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('activity_reports', $filename, 'public');

            // Update the report record
            $report->update([
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'submitted_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Report updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Report update failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'report_id' => $reportId,
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update report. Please try again.');
        }
    }

    /**
     * Delete a report
     */
    public function deleteReport(OrganizationApplication $application, $reportId)
    {
        try {
            // Ensure this is a Plan of Activities form
            if ($application->form_type !== 'LSPU-OSAS-SF-004') {
                abort(404, 'Reports are only available for Plan of Activities submissions.');
            }

            // Ensure user owns this application or is admin
            if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this application.');
            }

            $report = $application->activityReports()->findOrFail($reportId);

            // Check if report is approved - prevent deletion of approved reports
            if (strtolower($report->status) === 'approved') {
                return redirect()->back()
                    ->with('error', 'Cannot delete an approved report.');
            }

            // Delete the file from storage - use public disk for persistence
            if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }

            // Delete the report record
            $report->delete();

            // Redirect back to reports page with success message
            return redirect()->route('applications.reports', $application)
                ->with('success', 'Report deleted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Report deletion failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'report_id' => $reportId,
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete report. Please try again.');
        }
    }

    /**
     * Show the report feedback view page
     */
    public function showReportFeedback(OrganizationApplication $application, ActivityReport $report)
    {
        // Ensure user owns this application or is admin
        if (!auth()->user()->isAdmin() && $application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this application.');
        }

        // Ensure this report belongs to this application
        if ($report->organization_application_id !== $application->id) {
            abort(404, 'Report not found for this application.');
        }

        return Inertia::render('ReportFeedbackView', [
            'application' => $application,
            'report' => $report,
            'backUrl' => route('applications.reports', $application),
            'isAdmin' => auth()->user()->isAdmin(),
        ]);
    }
}