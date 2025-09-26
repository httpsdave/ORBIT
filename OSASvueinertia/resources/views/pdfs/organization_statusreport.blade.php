<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Status Report</title>
    <style>
        /* Set A4 landscape paper size for print */
        @page {
            size: A4 landscape;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 1.5cm;
            margin-right: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            font-size: 13px;
            margin: 0 0 0.5cm 0;
            padding-top: 0.3cm;
        }

        .content {
            flex: 1;
        }

        .logo {
            position: absolute;
            top: -0.3cm;
            left: -1cm;
            width: 200px;
            height: auto;
        }

        .university-name {
            max-width: 35%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }

        .calibri-text {
            font-family: Calibri, sans-serif;
        }

        .right-align {
            text-align: right;
        }

        .date-section {
            margin: 10px 0;
            text-align: right;
        }

        .addressee-section {
            margin: 15px 0;
            text-align: left;
        }

        .greeting {
            margin: 15px 0;
        }

        .intro-paragraph {
            margin: 15px 0;
            text-align: justify;
            text-indent: 1.27cm;
        }

        /* Table styling for activities */
        .activities-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }

        .activities-table th,
        .activities-table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .activities-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .activities-table .text-left {
            text-align: left;
        }

        .activities-table .small-col {
            width: 8%;
        }

        .activities-table .medium-col {
            width: 12%;
        }

        .activities-table .large-col {
            width: 15%;
        }

        .activities-table .xlarge-col {
            width: 20%;
        }

        .signature-section {
            margin-top: 30px;
            text-align: right;
            padding-right: 100px;
        }

        .signature-line {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
            margin: 10px 0;
        }

        .signature-title {
            display: block;
            width: 200px;
            text-align: center;
            margin-top: 5px;
        }

        .footer {
            position: absolute;
            bottom: -5px;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, sans-serif;
        }

        .footer-left {
            position: absolute;
            left: 0.1cm;
            bottom: -5px;
        }

        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -5px;
        }

        .footer-right {
            position: absolute;
            right: 0.1cm;
            bottom: -5px;
        }

        p {
            margin: 3px 0;
            word-wrap: break-word;
            line-height: 1.15;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span class="calibri-text" style="font-size:10pt;">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="calibri-text" style="font-size:10pt;">Province of Laguna</span><br>
        <br>
        <strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
        <br>
        <span class="sub-header"><strong>ACTIVITY STATUS REPORT</strong></span>
    </div>

    <div class="content">
        <div class="date-section">
            <p><u><strong>{{ isset($application->report_date) ? \Carbon\Carbon::parse($application->report_date)->format('F d, Y') : \Carbon\Carbon::now()->format('F d, Y') }}</strong></u></p>
            <p style="margin-top: 0; text-align: right; padding-right: 50px;">Date</p>
        </div>

        <div class="addressee-section">
            <p><strong>THE DIRECTOR/CHAIRPERSON</strong><br>
            Office of Student Affairs and Services<br>
            LSPU</p>
        </div>

        <div class="greeting">
            <p>Sir/Madam:</p>
        </div>

        <div class="intro-paragraph">
            <p>In compliance with the requirements of the Office of Student Affairs and Services, we respectfully submit the Status Report on the Plan of Activities for Academic Year {{ isset($application->academic_year_start) ? $application->academic_year_start : '2024' }}-{{ isset($application->academic_year_end) ? $application->academic_year_end : '2025' }}. The report presents activities conducted as planned, those not implemented with justifications, and additional activities carried out beyond the approved plan.</p>
        </div>

        <div style="text-align: center; margin: 0; padding: 0;">
            <strong>ACTIVITIES UNDER THE APPROVED PLAN OF ACTIVITIES</strong>
        </div>
        <table class="activities-table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th style="width: 18%;">Title of Activity / Program</th>
                    <th style="width: 10%;">Planned / Target Date</th>
                    <th style="width: 10%;">Actual Date Conducted</th>
                    <th style="width: 8%;">Proposed Budget</th>
                    <th style="width: 8%;">Actual Expenditure</th>
                    <th style="width: 12%;">Target No. of Participants</th>
                    <th style="width: 12%;">Actual No. of Participants</th>
                    <th style="width: 10%;">Remarks/ Status</th>
                    <th style="width: 12%;">Justification / Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin: 20px 0 0 0; padding: 0;">
            <strong>ACTIVITIES NOT IN THE APPROVED PLAN OF ACTIVITIES</strong>
        </div>
        <table class="activities-table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th style="width: 18%;">Title of Activity / Program</th>
                    <th style="width: 10%;">Planned / Target Date</th>
                    <th style="width: 10%;">Actual Date Conducted</th>
                    <th style="width: 8%;">Proposed Budget</th>
                    <th style="width: 8%;">Actual Expenditure</th>
                    <th style="width: 12%;">Target No. of Participants</th>
                    <th style="width: 12%;">Actual No. of Participants</th>
                    <th style="width: 10%;">Remarks/ Status</th>
                    <th style="width: 12%;">Justification / Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-left">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <p>Respectfully submitted,</p>
            <div style="margin-top: 30px;">
                <p><span class="signature-line"><strong>{{ isset($application->president_name) ? $application->president_name : 'ORGANIZATION PRESIDENT NAME' }}</strong></span></p>
                <p><span class="signature-title">Organization President</span></p>
            </div>
            <div style="margin-top: 20px;">
                <p><span class="signature-line"><strong>{{ isset($application->organization_name) ? $application->organization_name : 'ORGANIZATION NAME' }}</strong></span></p>
                <p><span class="signature-title">Name of Organization</span></p>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-STATUS</div>
        <div class="footer-center">Rev.1</div>
        <div class="footer-right">26 September 2025</div>
    </div>
</body>
</html>