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
            font-size: 15px;
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

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
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
            margin-top: 40px;
            clear: both;
        }

        .noted {
            text-align: center;
            margin-top: 30px;
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
            table-layout: fixed;
            overflow-wrap: break-word;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
            vertical-align: top;
            min-height: 60px;
            word-wrap: break-word;
        }

        th {
            font-weight: bold;
            font-size: 10pt;
            background-color: #f5f5f5;
        }

        /* Column width distribution */
        table th:nth-child(1), table td:nth-child(1) { width: 15%; } /* OBJECTIVE */
        table th:nth-child(2), table td:nth-child(2) { width: 15%; } /* ACTIVITIES */
        table th:nth-child(3), table td:nth-child(3) { width: 25%; } /* BRIEF DESCRIPTION */
        table th:nth-child(4), table td:nth-child(4) { width: 15%; } /* PERSONS INVOLVED */
        table th:nth-child(5), table td:nth-child(5) { width: 15%; } /* TARGET DATE */
        table th:nth-child(6), table td:nth-child(6) { width: 15%; } /* BUDGET */

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
            max-width: 55%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }

        .calibri-text {
            font-family: Calibri, sans-serif;
            font-weight: normal;
        }

        /* Signatures section */
        .signatures-section {
            margin-top: auto;
            padding-top: 20px;
        }
    </style>
</head>
<body>

@foreach($activities as $index => $activity)
    <div class="page">
        <div class="header">
            <img src="{{ base_path('public/images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
            <span class="calibri-text">Republic of the Philippines</span><br>
            <img src="{{ base_path('public/images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
            <span class="calibri-text">Province of Laguna</span><br>
            <br>
            <div style="margin-top: 15px; text-decoration: underline;">{{ $application->organization_name }}</div>
            <div class="subtitle">PLAN OF ACTIVITIES</div>
            <div class="semester">Semester AY {{ $application->academic_year_start }}-{{ $application->academic_year_end }}</div>
        </div>

        <div class="content">
            <table>
                <tr>
                    <th>OBJECTIVE</th>
                    <th>ACTIVITIES</th>
                    <th>BRIEF DESCRIPTION</th>
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
                    <td>{{ number_format($activity->budget, 2) }}</td>
                </tr>
            </table>
            
            <div class="signatures-section">
                <!-- First signature row with President and Secretary -->
                <div class="signature-container clearfix">
                    <div class="signature-left">
                        <div class="signature-line">{{ $application->president_name }}</div>
                        <p>Organization President</p>
                    </div>
                    <div class="signature-right">
                        <div class="signature-line">{{ $application->secretary_name ?? 'N/A' }}</div>
                        <p>Organization Secretary</p>
                    </div>
                </div>
                
                <div class="noted">
                    <p>Noted:</p>
                </div>
                
                <!-- Second signature row with Faculty Adviser and Dean -->
                <div class="signature-container clearfix">
                    <div class="signature-left">
                        <div class="signature-line">{{ $application->adviser_name ?? 'N/A' }}</div>
                        <p>Faculty Adviser(s)</p>
                    </div>
                    <div class="signature-right">
                        <div class="signature-line">{{ $application->dean_name ?? 'N/A' }}</div>
                        <p>Dean/Assoc. Dean of College</p>
                    </div>
                </div>
                
                <div class="recommendation">
                    <p>Recommending Approval:</p>
                    <div class="signature-line">{{ $application->coordinator_name ?? 'N/A' }}</div>
                    <p>Coordinator, Student Organization Unit</p>
                </div>
                
                <div class="signature-block">
                    <p>Approved/Disapproved:</p>
                    <div class="signature-line">{{ $application->director_name ?? 'N/A' }}</div>
                    <p>Director, Office of Student Affairs and Services</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-left">LSPU-OSAS-SF-004</div>
            <div class="footer-center">Rev. 1</div>
            <div class="footer-right">09 November 2020</div>
        </div>
    </div>
@endforeach

</body>
</html>
