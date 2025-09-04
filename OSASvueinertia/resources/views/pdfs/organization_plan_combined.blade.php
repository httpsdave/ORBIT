<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan of Activities</title>
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
            font-size: 11pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .header {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 0.5cm 0;
            padding-top: 0.5cm;
        }

        .subtitle {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 1cm 0 0.5cm 0;
        }

        .semester {
            text-align: center;
            font-size: 12pt;
            margin: 0.5cm 0;
        }

        .section { 
            margin-bottom: 5px;
        }

        .content {
            flex: 1;
        }

        /* Clear signature styling with left-right positioning */
        .signature-line { 
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
            margin: 0 auto;
        }

        .signature-container {
            width: 100%;
            margin-top: 20px;
            clear: both;
        }

        .signature-left {
            float: left;
            width: 45%;
            text-align: center;
        }

        .signature-right {
            float: right;
            width: 45%;
            text-align: center;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .signature-block {
            margin-top: 30px;
            text-align: center;
            clear: both;
        }

        .recommendation {
            text-align: center;
            margin-top: 70px; /* Increased margin to move this lower */
            clear: both;
        }

        .noted {
            text-align: center;
            margin-top: 20px;
            clear: both;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        /* Improved table styling for responsiveness */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.5cm 0;
            table-layout: fixed; /* Fixed layout ensures columns respect width settings */
            overflow-wrap: break-word; /* Ensures text wraps within cells */
        }

        table, th, td {
            border: 1px solid black;
        }

        th {
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 10pt;
            height: auto;
            word-wrap: break-word;
            hyphens: auto;
        }

        td {
            padding: 3px 6px; /* Reduced padding for better space utilization */
            text-align: center;
            vertical-align: top; /* Text starts at the top of each cell */
            height: auto; /* Allow height to grow naturally */
            word-wrap: break-word;
            hyphens: auto;
        }

        /* Optimized column width distribution for better space usage */
        table th:nth-child(1), table td:nth-child(1) { width: 18%; } /* OBJECTIVE */
        table th:nth-child(2), table td:nth-child(2) { width: 16%; } /* ACTIVITIES */
        table th:nth-child(3), table td:nth-child(3) { width: 24%; } /* BRIEF DESCRIPTION */
        table th:nth-child(4), table td:nth-child(4) { width: 16%; } /* PERSONS INVOLVED */
    table th:nth-child(5), table td:nth-child(5) { width: 13%; min-width: 80px; max-width: 110px; } /* TARGET DATE */
        table th:nth-child(6), table td:nth-child(6) { width: 14%; } /* BUDGET */

        /* Remove forced height and optimize for content */

        /* Ensure content doesn't overflow page */
        .content {
            flex: 1;
            margin-bottom: 20px; /* Reduced space for signatures and footer */
        }

        /* Signature positioning adjustments for single activity pages */
        .signature-container {
            width: 100%;
            margin-top: 30px;
            clear: both;
        }

        .noted {
            text-align: center;
            margin-top: 30px;
            clear: both;
        }

        .recommendation {
            text-align: center;
            margin-top: 40px;
            clear: both;
        }

        .signature-block {
            text-align: center;
            margin-top: 30px;
            clear: both;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
        }

        .footer-left {
            position: absolute;
            left: -1.0cm;
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
            right: -1.0cm;
            bottom: 0;
        }

        .university-name {
            max-width: 45%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }
        .calibri-text {
            font-family: Calibri, sans-serif;
            font-weight: normal;
        }

        /* Print-specific styles */
        @media print {
            table { 
                page-break-inside: avoid; 
                margin-bottom: 20px;
            }
            tr { 
                page-break-inside: avoid; 
                page-break-after: auto; 
            }
            td { 
                vertical-align: top; 
                word-wrap: break-word;
            }
            .signature-container,
            .noted,
            .recommendation,
            .signature-block {
                page-break-inside: avoid;
            }
        }

        /* Page break utilities */
        .page-break {
            page-break-before: always;
        }
        
        .no-page-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@foreach($activities as $index => $activity)
    <div class="page">
        <div class="header">
            <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">

            <span class="calibri-text"style="font-size:10pt;">Republic of the Philippines</span><br>
            <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
            <span class="calibri-text"style="font-size:10pt;">Province of Laguna</span><br>
            <br>
            <p class="office-title" style="font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:5px;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
            <div class="subtitle" style="margin-top:15px; font-size:13pt;">PLAN OF ACTIVITIES</div>
            <div style="margin-top: 15px; text-align: center;">
                <div class="signature-line" style="margin-bottom:0px; min-width:330px;"><strong>{{ $application->organization_name }}</strong></div>
                <div class="title-under-signature" style="margin-top:2px;">Name of Organization</div>
            </div>
            <div style="text-align:center; margin-top:10px;">
                <span class="signature-line" style="min-width:20px; margin-bottom:-2px; line-height:10px; padding:0 0 0 0;">
                    <span style="position:relative; top:0px;"><strong>{{ $application->semester ?? '1st' }}</strong></span>
                </span> Semester AY 20<span class="signature-line" style="min-width:20px; margin-bottom:-2px; margin-top:-1px; line-height:10px; padding:0 0 0 0;">
                    <span style="position:relative; top:1px;"><strong>{{ $application->academic_year_start ?? '24' }}</strong></span>
                </span>-20<span class="signature-line" style="min-width:20px; margin-bottom:-2px; margin-top:-1px; line-height:10px; padding:0 0 0 0;">
                    <span style="position:relative; top:1px;"><strong>{{ $application->academic_year_end ?? '25' }}</strong></span>
                </span>
            </div>
                <!-- Removed Activity (number current ac) of (number of activity) -->
        </div>

        <div class="content" style="margin-top:-10px;">
            <table>
                <tr>
                    <th>OBJECTIVE</th>
                    <th>ACTIVITIES</th>
                    <th>BRIEF <br> DESCRIPTION</th>
                    <th>PERSONS INVOLVED</th>
                    <th>TARGET DATE</th>
                    <th>BUDGET</th>
                </tr>
                <tr>
                    <td>{!! $activity->objective !!}</td>
                    <td>{!! $activity->name !!}</td>
                    <td>{!! $activity->description !!}</td>
                    <td>{!! $activity->persons_involved !!}</td>
                    <td>{{ \Carbon\Carbon::parse($activity->target_date)->format('F d, Y') }}</td>
                    <td>{{ $activity->budget ?? '' }}</td>
                </tr>
            </table>
            
            <!-- Prepared by label -->
            <div style="margin-top: 30px; margin-bottom: -10px; text-align: left; font-family: inherit; padding-left: 5px;">Prepared by:</div>
            <!-- First signature row with President and Secretary -->
            <div class="signature-container clearfix">
                <div class="signature-left">
                    <div class="signature-line" style="margin-bottom:0px;"><strong>{{ $application->president_name }}</strong></div>
                    <p style="margin-top:2px;">Organization President</p>
                </div>
                <div class="signature-right">
                    <div class="signature-line" style="margin-bottom:0px;"><strong>{{ $application->secretary_name ?? 'N/A' }}</strong></div>
                    <p style="margin-top:2px;">Organization Secretary</p>
                </div>
            </div>
            
            <div class="noted" style="text-align:left; margin-top:5px;">
                <p style="margin-left:5px;"><strong>Noted:</strong></p>
            </div>
            
            <!-- Second signature row with Faculty Adviser -->
            <div class="signature-container clearfix">
                <div style="width: 100%; text-align: left;">
                    <div class="signature-line" style="margin-bottom:0px; white-space:nowrap; min-width:200px; display:inline-block; text-align: center;margin-left: 35px; font-size: {{ strlen(trim((isset($application->adviser_prefix) && $application->adviser_prefix ? $application->adviser_prefix . ' ' : '') . ($application->adviser_name ?? '') . (isset($application->adviser_suffix) && $application->adviser_suffix ? ', ' . $application->adviser_suffix : ''))) > 25 ? '10pt' : '11pt' }}; word-wrap: break-word; overflow-wrap: break-word;"><strong>{{ trim((isset($application->adviser_prefix) && $application->adviser_prefix ? $application->adviser_prefix . ' ' : '') . ($application->adviser_name ?? 'N/A') . (isset($application->adviser_suffix) && $application->adviser_suffix ? ', ' . $application->adviser_suffix : '')) }}</strong></div>
                    <p style="margin-top:2px; text-align: left; width: 180px;margin-left:75px">Organization Adviser(s)</p>
                </div>
            </div>
            
            <!-- Third signature row with Dean -->
            <div class="signature-container clearfix" style="margin-top:5px;">
                <div style="width: 100%; text-align: left;">
                    <div class="signature-line" style="margin-bottom:0px; white-space:nowrap; min-width:200px; display:inline-block; text-align: center;margin-left: 35px; font-size: {{ strlen(trim((isset($application->dean_prefix) && $application->dean_prefix ? $application->dean_prefix . ' ' : '') . ($application->dean_name ?? '') . (isset($application->dean_suffix) && $application->dean_suffix ? ', ' . $application->dean_suffix : ''))) > 25 ? '10pt' : '11pt' }}; word-wrap: break-word; overflow-wrap: break-word; height: 1.2em;"><strong>{!! trim((isset($application->dean_prefix) && $application->dean_prefix ? $application->dean_prefix . ' ' : '') . ($application->dean_name ?? '') . (isset($application->dean_suffix) && $application->dean_suffix ? ', ' . $application->dean_suffix : '')) ?: '&nbsp;' !!}</strong></div>
                    <p style="margin-top:2px; text-align: left; width: 180px;margin-left: 85px;">Dean/Assoc. Dean</p>
                </div>
            </div>
            
            <div class="recommendation" style="margin-top:20px;">
                <p><strong>Recommending Approval:</strong></p>
                <div class="signature-line" style="min-width:290px; margin-bottom:0px;"><strong>{{ $application->coordinator_name ?? 'N/A' }}</strong></div>
                <p style="margin-top:2px; margin-bottom:20px;">Coordinator, Student Organization Unit</p>
            </div>
            
            <div class="signature-block" style="margin-top:20px;">
                <p><strong>Approved/Disapproved:</strong></p>
                <div class="signature-line" style="min-width:415px; margin-bottom:0px;"><strong>{{ $application->director_name ?? 'N/A' }}</strong></div>
                <p style="margin-top:2px;">Director/Chairperson, Office of Student Affairs and Services</p>
            </div>
        </div>

        <div class="footer" style="position: absolute; bottom: -5px; width: 100%; height: 20px; line-height: 20px; font-size: 10pt; font-family: Calibri, sans-serif;">
            <div class="footer-left" style="position: absolute; left: .1cm; bottom: -5px;">LSPU-OSAS-SF-004</div>
            <div class="footer-center" style="position: absolute; left: 50%; transform: translateX(-50%); bottom: -5px;">Rev. 1</div>
            <div class="footer-right" style="position: absolute; right: .1cm; bottom: -5px;">09 November 2020</div>
        </div>
    </div>
@endforeach

</body>
</html>
