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
        $applications = OrganizationApplication::all();
        return Inertia::render('OrganizationApplications/Index', ['applications' => $applications]);
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
    }elseif ($request->form_type === 'LSPU-OSAS-SF-006') {
        $validationRules = array_merge($validationRules, [
            'student_name' => 'required|string|max:255',
            'course_year_section' => 'required|string|max:255',
            'position_rank' => 'nullable|string|max:255',
            'is_bonafide' => 'required|boolean',
            'is_not_academic_probation' => 'required|boolean',
            'is_not_disciplinary_probation' => 'required|boolean',
            'has_position' => 'required|boolean',
        ]);
        
    }
    
    
    
    $request->validate($validationRules);
    
    $data = $request->all();
    
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
    
    
    $application = OrganizationApplication::create($data);

    // Save activities if this is the Plan of Activities form
    if ($request->form_type === 'LSPU-OSAS-SF-004' && $request->has('activities')) {
        foreach ($request->activities as $activityData) {
            $application->activities()->create($activityData);
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

    public function exportPdf(OrganizationApplication $application)
    {
        $pdf = Pdf::loadView('pdfs.organization_application', compact('application'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('Application_' . $application->organization_name . '.pdf');
    }
    public function exportRenewalPdf(OrganizationApplication $application)
    {
        $pdf = Pdf::loadView('pdfs.organization_renewal', compact('application'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('Renewal_' . $application->organization_name . '.pdf');
    }

    public function exportCommitmentPdf(OrganizationApplication $application)
    {
        $pdf = Pdf::loadView('pdfs.organization_commitment', compact('application'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('Commitment_' . $application->organization_name . '.pdf');
    }

    public function exportPlanPdf(OrganizationApplication $application)
    {
        $application->load('activities'); // Eager load activities for the PDF

        // Pass both application and activities to the PDF view
        $pdf = Pdf::loadView('pdfs.organization_plan', ['application' => $application, 'activities' => $application->activities])
                ->setPaper('A4', 'portrait');

        return $pdf->download('Plan_' . $application->organization_name . '.pdf');
    }

    public function exportCertificationPdf(OrganizationApplication $application)
    {
        $pdf = Pdf::loadView('pdfs.organization_certification', compact('application'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('Certification_' . $application->organization_name . '.pdf');
    }




}
