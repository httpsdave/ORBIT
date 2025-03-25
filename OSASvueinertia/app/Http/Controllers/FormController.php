<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use Dompdf\Dompdf;
use Dompdf\Options;

class FormController extends Controller
{
    /**
     * Display the form page
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Form');
    }
    
    /**
     * Generate PDF from submitted form data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'organizationName' => 'required|string',
            'presidentName' => 'required|string',
            'adviserName' => 'required|string',
            'deanName' => 'required|string',
            'coordinatorName' => 'required|string',
            'directorName' => 'required|string',
            'applicationType' => 'required|in:recognition,renewal',
        ]);
        
        // Format the date
        $date = date('F j, Y', strtotime($validated['date']));
        
        $applicationType = $validated['applicationType'];
        
        // Generate the HTML content
        $html = view('pdf.organization-form', [
            'date' => $date,
            'organizationName' => $validated['organizationName'],
            'presidentName' => $validated['presidentName'],
            'adviserName' => $validated['adviserName'],
            'deanName' => $validated['deanName'],
            'coordinatorName' => $validated['coordinatorName'],
            'directorName' => $validated['directorName'],
            'applicationType' => $applicationType,
        ])->render();
        
        // PDF generation options
        $options = new Options();
        $options->set('defaultFont', 'Times New Roman');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        // Create PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Stream the file to the browser
        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="LSPU-OSAS-SF-001.pdf"',
        ]);
    }
}