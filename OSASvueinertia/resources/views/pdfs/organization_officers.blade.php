<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Officers - Student Organization</title>
    <style>
        /* Set A4 paper size for print */
        @page {
            size: A4;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 2.54cm;
            margin-right: 2.54cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .header {
            text-align: center;
            font-size: 12pt;
            font-weight: normal;
            margin: 0;
            padding-top: 0.3cm;
            line-height: 1.2;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        .organization-details {
            text-align: center;
            margin-top: 0.1cm;
            margin-bottom: 0.2cm;
        }
        
        .organization-details p {
            margin: 0.1cm 0;
        }

        .list-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 0.3cm;
            font-size: 14pt;
        }

        .officer-details {
            float: left;
            padding-top: 0.4cm;
        }

        .list-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 0.5cm;
            font-size: 13pt;
        }

        .officer-row {
            margin-bottom: 0.5cm;
            clear: both;
            height: 5.2cm;
        }

        .officer-details .field-row:first-child {
            margin-top: 0.2cm;
        }

        .photo-box {
            width: 2in;
            height: 2in;
            border: 1px solid black;
            text-align: center;
            line-height: 2in;
            float: left;
            margin-right: 0.5cm;
        }

        .officer-details {
            float: left;
        }

        .field-row {
            margin-bottom: 0.5cm;
        }

        .field-label {
            display: inline-block;
            width: 120px;
        }

        .field-colon {
            display: inline-block;
            width: 10px;
            margin-right: 10px;
        }

        .field-value {
            display: inline-block;
            min-width: 200px;
        }

        /* Footer styling - compatible with DOMPDF */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, sans-serif;
        }

        .footer-left {
            position: absolute;
            left: -1.0cm; /* Match the left margin */
            bottom: 0;
        }

        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
        }

        .footer-right {
            position: absolute;
            right: -1.0cm; /* Match the right margin */
            bottom: 0;
        }

        .page-break {
            page-break-before: always;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .university-name {
            max-width: 55%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }

        .calibri-text {
            font-family: Calibri, sans-serif;
        }
        
        .office-heading {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 0.5cm;
        }
        /* DOMPDF fixed footer compatibility */
        @page {
            margin-bottom: 30px; /* Space for footer */
        }
        
        /* Original footer spacing */
        .content {
            margin-bottom: 0.5cm;
        }
    </style>
</head>
<body> 
    <!-- Footer that will appear on all pages with DOMPDF compatibility -->
    <!--
    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-007</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>
    
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Calibri", "normal");
            $size = 10;
            $y = $pdf->get_height() - 20;
            
            // Left footer
            $pdf->page_text(20, $y, "LSPU-OSAS-SF-007", $font, $size);
            
            // Center footer
            $text = "Rev. 1";
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $pdf->page_text($x, $y, $text, $font, $size);
            
            // Right footer
            $text = "09 November 2020";
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = $pdf->get_width() - $width - 20;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
    -->
    
    <!-- First Page -->
    <div class="header">
    <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span class="calibri-text">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="calibri-text">Province of Laguna</span><br>
        <span class="office-heading">OFFICE OF STUDENT AFFAIRS AND SERVICES</span>
    </div>
    
    <!-- Organization details -->
    <div class="organization-details">
        <p> {{ $application->organization_name ?? '____________________' }}</p>
        <p>A.Y. 20{{ $application->academic_year_start ?? '2024' }}-20{{ $application->academic_year_end ?? '2025' }}</p>
    </div>
    
    <!-- List title -->
    <div class="list-title">LIST OF OFFICERS</div>
    
    <!-- Officer entries -->
    <div class="content">
        <!-- Officer 1 -->
        <div class="officer-row clearfix">
            <div class="photo-box">
                @if(isset($officers[0]) && $officers[0]->photo_path)
                    <img src="{{ storage_path('app/public/' . $officers[0]->photo_path) }}" alt="Officer Photo" width="100%" height="100%">
                @else
                    2X2
                @endif
            </div>
            <div class="officer-details">
                <div class="field-row">
                    <div class="field-label">Name</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[0]) ? $officers[0]->student_name : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Position</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[0]) ? $officers[0]->position : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Student I.D. No.</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[0]) ? $officers[0]->student_number : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Signature</div>
                    <div class="field-colon">:</div>
                    <div class="field-value"></div>
                </div>
            </div>
        </div>
        
        <!-- Officer 2 -->
        <div class="officer-row clearfix">
            <div class="photo-box">
                @if(isset($officers[1]) && $officers[1]->photo_path)
                    <img src="{{ storage_path('app/public/' . $officers[1]->photo_path) }}" alt="Officer Photo" width="100%" height="100%">
                @else
                    2X2
                @endif
            </div>
            <div class="officer-details">
                <div class="field-row">
                    <div class="field-label">Name</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[1]) ? $officers[1]->student_name : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Position</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[1]) ? $officers[1]->position : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Student I.D. No.</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[1]) ? $officers[1]->student_number : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Signature</div>
                    <div class="field-colon">:</div>
                    <div class="field-value"></div>
                </div>
            </div>
        </div>
        
        <!-- Officer 3 -->
        <div class="officer-row clearfix">
            <div class="photo-box">
                @if(isset($officers[2]) && $officers[2]->photo_path)
                    <img src="{{ storage_path('app/public/' . $officers[2]->photo_path) }}" alt="Officer Photo" width="100%" height="100%">
                @else
                    2X2
                @endif
            </div>
            <div class="officer-details">
                <div class="field-row">
                    <div class="field-label">Name</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[2]) ? $officers[2]->student_name : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Position</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[2]) ? $officers[2]->position : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Student I.D. No.</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[2]) ? $officers[2]->student_number : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Signature</div>
                    <div class="field-colon">:</div>
                    <div class="field-value"></div>
                </div>
            </div>
        </div>
        
        <!-- Officer 4 -->
        <div class="officer-row clearfix">
            <div class="photo-box">
                @if(isset($officers[3]) && $officers[3]->photo_path)
                    <img src="{{ storage_path('app/public/' . $officers[3]->photo_path) }}" alt="Officer Photo" width="100%" height="100%">
                @else
                    2X2
                @endif
            </div>
            <div class="officer-details">
                <div class="field-row">
                    <div class="field-label">Name</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[3]) ? $officers[3]->student_name : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Position</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[3]) ? $officers[3]->position : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Student I.D. No.</div>
                    <div class="field-colon">:</div>
                    <div class="field-value">{{ isset($officers[3]) ? $officers[3]->student_number : '' }}</div>
                </div>
                <div class="field-row">
                    <div class="field-label">Signature</div>
                    <div class="field-colon">:</div>
                    <div class="field-value"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional officers with proper page breaks and headers -->
    @if(isset($officers) && count($officers) > 4)
        @for($i = 4; $i < count($officers); $i++)
            @if($i % 4 == 0)
                <!-- Create page break after every 4 officers -->
                <div class="page-break"></div>
                
                <!-- Header on new page -->
                <div class="header">
                    <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
                    <span class="calibri-text">Republic of the Philippines</span><br>
                    <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
                    <span class="calibri-text">Province of Laguna</span><br>
                    <span class="office-heading">OFFICE OF STUDENT AFFAIRS AND SERVICES</span>
                </div>
                
                <!-- Organization details on new page -->
                <div class="organization-details">
                    <p>{{ $application->organization_name ?? '____________________' }}</p>
                    <p>A.Y. 20{{ $application->academic_year_start ?? '2024' }}-20{{ $application->academic_year_end ?? '2025' }}</p>
                </div>
                
                <!-- List title on new page -->
                <div class="list-title">LIST OF OFFICERS</div>
            @endif
            
            <div class="officer-row clearfix">
                <div class="photo-box">
                    @if(isset($officers[$i]) && $officers[$i]->photo_path)
                        <img src="{{ storage_path('app/public/' . $officers[$i]->photo_path) }}" alt="Officer Photo" width="100%" height="100%">
                    @else
                        2X2
                    @endif
                </div>
                <div class="officer-details">
                    <div class="field-row">
                        <div class="field-label">Name</div>
                        <div class="field-colon">:</div>
                        <div class="field-value">{{ isset($officers[$i]) ? $officers[$i]->student_name : '' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Position</div>
                        <div class="field-colon">:</div>
                        <div class="field-value">{{ isset($officers[$i]) ? $officers[$i]->position : '' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Student I.D. No.</div>
                        <div class="field-colon">:</div>
                        <div class="field-value">{{ isset($officers[$i]) ? $officers[$i]->student_number : '' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Signature</div>
                        <div class="field-colon">:</div>
                        <div class="field-value"></div>
                    </div>
                </div>
            </div>
        @endfor
    @endif
</body>
</html>