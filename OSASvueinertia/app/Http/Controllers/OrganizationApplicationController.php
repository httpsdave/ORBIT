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
    }
    
    $request->validate($validationRules);
    
    $data = $request->all();
    
    // Set default values for missing fields based on form type
    if ($request->form_type === 'LSPU-OSAS-SF-002') {
        $data['application_date'] = now(); // Use current date for renewal forms
        $data['director_name'] = $data['chairperson_name']; // Use chairperson name for director
    }
    
    OrganizationApplication::create($data);

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


}
