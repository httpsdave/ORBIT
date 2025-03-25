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
        $request->validate([
            'form_type' => 'required|string',
            'organization_name' => 'required|string|max:255',
            'president_name' => 'required|string|max:255',
            'application_date' => 'required|date',
            'adviser_name' => 'required|string|max:255',
            'dean_name' => 'required|string|max:255',
            'coordinator_name' => 'required|string|max:255',
            'director_name' => 'required|string|max:255', // Ensure this is validated
            'status' => 'string|in:Pending,Approved,Disapproved',
        ]);

        OrganizationApplication::create($request->all());

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

}
