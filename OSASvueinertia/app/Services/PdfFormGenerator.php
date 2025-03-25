<?php

namespace App\Services;

use TCPDF;

class PdfFormGenerator
{
    /**
     * Generate a PDF document with fillable form fields
     *
     * @param array $data Default data for the form
     * @param array $editableFields Configuration for editable fields
     * @return string Path to the generated PDF file
     */
    public function generatePdf(array $data = [], array $editableFields = [])
    {
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Your Application');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Document with Fillable Fields');
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Add company logo
        $logoPath = public_path('images/logo.png'); // Adjust path as needed
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, 10, 50, 0, 'PNG');
        }
        
        // Add title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, 'Your Document Title', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        
        // Add spacing
        $pdf->Ln(10);
        
        // Add static content
        $pdf->writeHTML($this->getStaticContent($data));
        
        // Add form fields
        $this->addFormFields($pdf, $editableFields, $data);
        
        // Save the PDF
        $pdfPath = storage_path('app/temp/' . uniqid('form_') . '.pdf');
        $pdf->Output($pdfPath, 'F');
        
        return $pdfPath;
       

        return $pdf->Output('', 'S'); // 'S' returns the PDF as a string (not saving)

    }
    
    /**
     * Get static content for the PDF
     * 
     * @param array $data Data to include in static content
     * @return string HTML content
     */
    protected function getStaticContent(array $data)
    {
        // This is your template with non-editable parts
        $html = '
        <table cellspacing="0" cellpadding="1" border="0">
            <tr>
                <td width="50%"><strong>Document Number:</strong> ' . ($data['document_number'] ?? 'DOC-' . date('Ymd')) . '</td>
                <td width="50%"><strong>Date:</strong> ' . ($data['date'] ?? date('Y-m-d')) . '</td>
            </tr>
        </table>
        <br>
        <p>This document is prepared for:</p>
        <p><strong>' . ($data['recipient_name'] ?? '') . '</strong></p>
        <p>' . ($data['recipient_address'] ?? '') . '</p>
        <br>
        <p>Please review the following information carefully and complete the editable fields:</p>
        <br>';
        
        return $html;
    }
    
    
    
    
    /**
     * Add form fields to the PDF
     * 
     * @param TCPDF $pdf PDF object
     * @param array $editableFields Configuration for editable fields
     * @param array $data Default values for fields
     * @return void
     */
    protected function addFormFields($pdf, array $editableFields, array $data)
    {
        foreach ($editableFields as $field) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $field['label'] . ':', 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            
            // Default value
            $defaultValue = $data[$field['name']] ?? '';
            
            switch ($field['type']) {
                case 'text':
                    // Single line text field
                    $pdf->TextField($field['name'], 180, 10, [
                        'value' => $defaultValue,
                        'name' => $field['name']
                    ]);
                    break;
                    
                case 'textarea':
                    // Multi-line text area
                    $pdf->TextField($field['name'], 180, 30, [
                        'value' => $defaultValue,
                        'multiline' => true,
                        'name' => $field['name']
                    ]);
                    break;
                    
                case 'checkbox':
                    // Checkbox
                    $pdf->CheckBox($field['name'], 5, false, [], [], $defaultValue ? 'Yes' : 'Off');
                    $pdf->Cell(50, 5, $field['checkboxLabel'] ?? 'Yes', 0, 1);
                    break;
                    
                case 'dropdown':
                    // Dropdown/select field
                    $pdf->ComboBox($field['name'], 180, 10, [
                        'value' => $defaultValue,
                        'options' => $field['options'] ?? []
                    ]);
                    break;
            }
            
            $pdf->Ln(10);
        }
    }
}