<?php

namespace App\Http\Controllers;

use App\Models\OrganizationApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;


class OrganizationApplicationController extends Controller
{
    public function index()
    {
        // If user is admin, show all applications
        if (auth()->user()->isAdmin()) {
            $applications = OrganizationApplication::all();
        } else {
            // For regular users, only show their own applications
            $applications = OrganizationApplication::where('user_id', auth()->id())->get();
            
            // If no applications are found, this will help with debugging
            if ($applications->isEmpty()) {
                // For testing, you might want to see all applications if none are found
                // $applications = OrganizationApplication::all();
            }
        }
        
        return Inertia::render('OrganizationApplications/Index', [
            'applications' => $applications,
            'userId' => auth()->id(), // Send user ID to the frontend for debugging
            'isAdmin' => auth()->user()->isAdmin() // Send admin status to frontend
        ]);
    }

    public function create()
    {
        return Inertia::render('OrganizationApplications/Create');
    }

    public function store(Request $request)
    {
        // Common fields validation
        $validationRules = [
            'form_type' => 'required|string',
            'organization_name' => 'required|string|max:255',
            'president_name' => 'required|string|max:255',
            'adviser_name' => 'required|string|max:255',
            'dean_name' => 'required|string|max:255',
            'coordinator_name' => 'required|string|max:255',
            'status' => 'string|in:Pending,Approved,Disapproved',
        ];
        
        // Add form-specific validation rules
        if ($request->form_type === 'LSPU-OSAS-SF-001') {
            $validationRules = array_merge($validationRules, [
                'application_date' => 'required|date',
                'director_name' => 'required|string|max:255',
                
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-002') {
            $validationRules = array_merge($validationRules, [
                'college' => 'required|string|max:255',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'chairperson_name' => 'required|string|max:255',
            ]);
        } elseif ($request->form_type === 'LSPU-OSAS-SF-003') {
            // Commitment form specific validation
            $validationRules = array_merge($validationRules, [
                'adviser_signature' => 'nullable|string|max:255',
                'adviser_college' => 'required|string|max:255',
                'adviser_rank' => 'required|string|max:255',
                'adviser_address' => 'required|string|max:255',
                'adviser_contact' => 'required|string|max:255',
                'form_date' => 'required|date',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                
            ]);
        }  elseif ($request->form_type === 'LSPU-OSAS-SF-004') {
            $validationRules = array_merge($validationRules, [
                'secretary_name' => 'required|string|max:255',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                
                'activities' => 'required|array|min:1',
                'activities.*.objective' => 'required|string|max:255',
                'activities.*.name' => 'required|string|max:255',
                'activities.*.description' => 'required|string|max:1000',
                'activities.*.persons_involved' => 'required|string|max:255',
                'activities.*.target_date' => 'required|date',
                'activities.*.budget' => 'required|numeric|min:0',
            ]);
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-005') {
            $validationRules = array_merge($validationRules, [
                'semester' => 'required|string|in:1st,2nd,Summer',
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'second_adviser' => 'nullable|string|max:255',
                'members' => 'required|array|min:1',
                'members.*.student_name' => 'required|string|max:255',
                'members.*.student_number' => 'required|string|max:50',
                'members.*.course_year_section' => 'required|string|max:255',
                'members.*.photo_path' => 'nullable|file|image|max:2048',
            ]);
        }
        elseif ($request->form_type === 'LSPU-OSAS-SF-006') {
            $validationRules = array_merge($validationRules, [
                'student_name' => 'required|string|max:255',
                'course_year_section' => 'required|string|max:255',
                'position_rank' => 'nullable|string|max:255',
                'is_bonafide' => 'required|boolean',
                'is_not_academic_probation' => 'required|boolean',
                'is_not_disciplinary_probation' => 'required|boolean',
                'has_position' => 'required|boolean',
            ]);
            
        }elseif ($request->form_type === 'LSPU-OSAS-SF-007') {
            $validationRules = array_merge($validationRules, [
                'academic_year_start' => 'required|string|max:10',
                'academic_year_end' => 'required|string|max:10',
                'officers' => 'required|array|min:1',
                'officers.*.student_name' => 'required|string|max:255',
                'officers.*.position' => 'required|string|max:255',
                'officers.*.student_number' => 'required|string|max:50',
                'officers.*.photo_path' => 'nullable|file|image|max:2048',
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
        }
        
        
        
        $data = $request->all();
        
        // Explicitly set user_id - make sure this line executes
        $data['user_id'] = auth()->id();
        
        // For debugging, you might want to log this
        \Log::info('Creating application for user: ' . auth()->id());
        // Set default values for missing fields based on form type
        if ($request->form_type === 'LSPU-OSAS-SF-002') 
        {
            $data['application_date'] = now(); // Use current date for renewal forms
            $data['director_name'] = $data['chairperson_name']; // Use chairperson name for director
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
            }
        
        
        $application = OrganizationApplication::create($data);

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

        return redirect()->route('applications.index');
    }

    public function edit(OrganizationApplication $application)
    {
        return Inertia::render('OrganizationApplications/Edit', ['application' => $application]);
    }

    public function update(Request $request, OrganizationApplication $application)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'president_name' => 'required|string|max:255',
            'application_date' => 'required|date',
        ]);
           

        $application->update($request->all());

        return redirect()->route('applications.index');
    }

    public function destroy(OrganizationApplication $application)
    {
        $application->delete();
        return redirect()->route('applications.index');
    }

    /* NEW METHOD: Update application status */
    public function updateStatus(Request $request, OrganizationApplication $application)
    {
        // Ensure only admins can update status
        // Ensure only admins can update status
            if (!auth()->user()->isAdmin()) {
                return redirect()->route('home')->with('error', 'Unauthorized. Only administrators can update application status.');
        }


        $request->validate([
            'status' => 'required|string|in:Pending,Approved,Disapproved',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $application->status = $request->status;
        
        // Save feedback if provided
        if ($request->has('feedback')) {
            $application->feedback = $request->feedback;
        }
        
        // Record who approved/disapproved the application
        $application->reviewed_by = auth()->id();
        $application->reviewed_at = now();
        
        $application->save();

        return redirect()->back()->with('success', 'Application status updated successfully');

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
        $application->load('activities'); // Eager load activities for the PDF

        // Pass both application and activities to the PDF view
        $pdf = Pdf::loadView('pdfs.organization_plan', ['application' => $application, 'activities' => $application->activities])
                ->setPaper('A4', 'portrait');
                
        $action = $request->query('action', 'download');
        
        if ($action === 'view') {
            return $pdf->stream('Plan_' . $application->organization_name . '.pdf');
        }
        
        return $pdf->download('Plan_' . $application->organization_name . '.pdf');
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
        $pdf = Pdf::loadView('pdfs.organization_certification', compact('application'))
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
}