<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activity Attendance Sheet</title>
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
            font-family: 'Calibri', sans-serif;
            font-size: 11pt;
            line-height: 1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            font-size: 11pt;
            margin: 0 0 0.3cm 0;
            padding-top: 0.3cm;
            position: relative;
        }

        .logo {
            position: absolute;
            top: -0.5cm; /* Adjust as needed */
            left: -2cm; /* Aligns with margin */
            width: 250px; /* Adjust size */
            height: auto;
        }

        .university-name {
            max-width: 60%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }
        .calibri-text {
            font-family: Calibri, sans-serif;
        }

        .title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            font-style: italic;
            margin: 0.2cm 0;
        }

        .content {
            flex: 1;
        }

        .form-row {
            margin: 0.2cm 0;
            clear: both;
        }

        .form-row:after {
            content: "";
            display: table;
            clear: both;
        }

        .form-field {
            display: inline-block;
            margin-right: 10px;
        }

        .form-field label {
            font-weight: bold;
        }

        .form-field .underline {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.2cm 0;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            height: 18px; /* reduced height for rows */
            font-size: 10pt;
        }

        th {
            font-weight: bold;
            font-size: 10pt;
        }

        /* Column width distribution - FIXED to match Image 2 */
        table th:nth-child(1), table td:nth-child(1) { width: 50%; } /* NAME */
        table th:nth-child(2), table td:nth-child(2) { width: 25%; } /* COURSE/YEAR & SECTION */
        table th:nth-child(3), table td:nth-child(3) { width: 25%; } /* SIGNATURE */

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

        /* Row number styling */
        .row-number {
            float: left;
            margin-right: 5px;
        }

        /* Print-specific styles */
        @media print {
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            td { vertical-align: top; }
        }
    </style>
</head>
<body>

    <div class="header">
    <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span class="calibri-text">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="calibri-text">Province of Laguna</span><br>
        <div style="margin-top: 5px; font-weight: bold;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
        <div class="title" style="margin-top: 5px;">STUDENT ACTIVITY ATTENDANCE SHEET</div>
        <span class= "underline" style="font-weight: bold; margin-top: 5px;">COLLEGE OF {{ $application->college ?? '' }}</span>
    </div>

    <div class="content">
        <div class="form-row" style="margin-top: 0.2cm;">
            <div class="form-field" style="float: left; width: 60%;">
                <label>ACTIVITY: </label>
                <span class="underline">{{ $application->activity_name ?? '' }}</span>
            </div>
            <div class="form-field" style="float: right; width: 30%; text-align: right;">
                <label>DATE: </label>
                <span class="underline" style="min-width: 100px;">{{ $application->activity_date ? \Carbon\Carbon::parse($application->activity_date)->format('F d, Y') : '' }}</span>
            </div>
        </div>

        <table>
            <tr>
                <th>NAME</th>
                <th>COURSE/YEAR &<br>SECTION</th>
                <th>SIGNATURE</th>
            </tr>
            @if(isset($application->attendees) && count($application->attendees) > 0)
                @foreach($application->attendees as $index => $attendee)
                <tr>
                    <td><span class="row-number">{{ $index + 1 }}.</span> {{ $attendee['name'] }}</td>
                    <td>{{ $attendee['course_year_section'] }}</td>
                    <td>&nbsp;</td>
                </tr>
                @endforeach
                
                @for($i = count($application->attendees) + 1; $i <= 35; $i++)
                <tr>
                    <td><span class="row-number">{{ $i }}.</span></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            @else
                @for($i = 1; $i <= 35; $i++)
                <tr>
                    <td><span class="row-number">{{ $i }}.</span></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            @endif
        </table>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-009</div>
        <div class="footer-center">Rev. 0</div>
        <div class="footer-right">10 August 2016</div>
    </div>

</body>
</html>