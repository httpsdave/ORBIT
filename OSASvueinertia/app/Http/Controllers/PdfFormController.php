<?php

namespace App\Http\Controllers;

use App\Services\PdfFormGenerator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PdfFormController extends Controller
{
    protected $pdfGenerator;
    
    /**
     * Constructor
     *
     * @param PdfFormGenerator $pdfGenerator
     */
    public function __construct(PdfFormGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }
    
    /**
     * Show the form creation page
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('PdfForms/Create', [
            'templates' => $this->getAvailableTemplates(),
        ]);
    }
    
    /**
     * Generate a PDF form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'template' => 'required|string',
            'data' => 'required|array',
        ]);
        
        // Get the editable fields for this template
        $editableFields = $this->getEditableFieldsForTemplate($validated['template']);
        
        // Generate the PDF
        $pdfPath = $this->pdfGenerator->generatePdf(
            $validated['data'],
            $editableFields
        );
        
        // Return the file for download
        return response()->download($pdfPath, 'fillable_form.pdf')->deleteFileAfterSend(true);
    }
    
    /**
     * Get the available templates
     *
     * @return array
     */
    protected function getAvailableTemplates()
    {
        return [
            'invoice' => 'Invoice Template',
            'contract' => 'Contract Template',
            'report' => 'Report Template',
            
        ];
    }
    
    /**
     * Get the editable fields for a specific template
     *
     * @param string $template
     * @return array
     */
    protected function getEditableFieldsForTemplate($template)
    {
        $templates = [
            'invoice' => [
                [
                    'name' => 'client_name',
                    'label' => 'Client Name',
                    'type' => 'text',
                ],
                [
                    'name' => 'client_email',
                    'label' => 'Client Email',
                    'type' => 'text',
                ],
                [
                    'name' => 'description',
                    'label' => 'Service Description',
                    'type' => 'textarea',
                ],
                [
                    'name' => 'agreed_payment',
                    'label' => 'Agreed to Payment Terms',
                    'type' => 'checkbox',
                    'checkboxLabel' => 'Yes, I agree to the payment terms',
                ],
            ],
            'contract' => [
                [
                    'name' => 'client_name',
                    'label' => 'Client Name',
                    'type' => 'text',
                ],
                [
                    'name' => 'special_terms',
                    'label' => 'Special Terms',
                    'type' => 'textarea',
                ],
                [
                    'name' => 'service_level',
                    'label' => 'Service Level',
                    'type' => 'dropdown',
                    'options' => ['Basic', 'Standard', 'Premium'],
                ],
            ],
            'report' => [
                [
                    'name' => 'assessment',
                    'label' => 'Assessment Details',
                    'type' => 'textarea',
                ],
                [
                    'name' => 'reviewer_name',
                    'label' => 'Reviewer Name',
                    'type' => 'text',
                ],
                [
                    'name' => 'reviewed_date',
                    'label' => 'Reviewed Date',
                    'type' => 'text',
                ],
            ],
            
            
            
        ];
        
        return $templates[$template] ?? [];
    }
}