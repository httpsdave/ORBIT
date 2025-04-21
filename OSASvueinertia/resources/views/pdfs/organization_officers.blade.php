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
        }

        .header {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 0.3cm 0;
            padding-top: 0.5cm;
        }

        .logo {
            position: absolute;
            top: 0.5cm;
            left: 2.54cm;
            width: 80px;
            height: auto;
        }

        .organization-details {
            text-align: center;
            margin-bottom: 0.3cm;
        }

        .officer-details {
            float: left;
            /* Add padding-top to move the text fields down to align with images */
            padding-top: 0.4cm;
        }

        .list-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 0.5cm;
            font-size: 14pt;
        }

        /* Optionally, you can also adjust the height of the officer row if needed */
        .officer-row {
            margin-bottom: 0.4cm;
            clear: both;
            height: 5.2cm;
        }

        /* If you need more precise control, adjust the first field's margin */
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
            margin-bottom: 0.4cm;
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

        /* DOMPDF compatible footer handling */
        .footer-left {
            position: fixed;
            left: 2.54cm;
            bottom: 0;
            font-size: 10pt;
        }

        .footer-center {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            font-size: 10pt;
        }

        .footer-right {
            position: fixed;
            right: 2.54cm;
            bottom: 0;
            font-size: 10pt;
        }

        .page-break {
            page-break-before: always;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="footer-left">LSPU-OSAS-SF-007</div>
    <div class="footer-center">Rev. 1</div>
    <div class="footer-right">09 November 2020</div>

    <!-- First Page -->
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        <br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES
    </div>
    
    <!-- Organization details -->
    <div class="organization-details">
        <p>Name of Organization: {{ $application->organization_name ?? '____________________' }}</p>
        <p>A.Y. {{ $application->academic_year_start ?? '2024' }}-{{ $application->academic_year_end ?? '2025' }}</p>
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
                    Republic of the Philippines<br>
                    Laguna State Polytechnic University<br>
                    Province of Laguna<br>
                    <br>
                    OFFICE OF STUDENT AFFAIRS AND SERVICES
                </div>
                
                <!-- Organization details on new page -->
                <div class="organization-details">
                    <p>Name of Organization: {{ $application->organization_name ?? '____________________' }}</p>
                    <p>A.Y. {{ $application->academic_year_start ?? '2024' }}-{{ $application->academic_year_end ?? '2025' }}</p>
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