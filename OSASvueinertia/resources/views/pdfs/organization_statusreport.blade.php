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
            position: relative;
            top: 40px;
        }

        .content {
            flex: 1;
        }

        .logo {
            position: absolute;
            top: calc(-0.5cm );
            left: -2cm;
            width: 250px;
            height: auto;
        }

        .university-name {
            max-width: 23%;
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
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
            min-height: 22px;
            height: 22px;
            line-height: 1.1;
        }

        .activities-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            min-height: 22px;
            height: 22px;
            line-height: 1.1;
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

        .signature {
            margin-top: 15px;
        }
        
        .signature p {
            margin: 3px 0;
        }

        /* Shift the entire signatures/approval block down by 20px visually without affecting layout flow */
        .signatures-wrapper {
            position: relative;
            top: 20px;
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

        .title-text {
            display: block;
            width: 200px;
            text-align: center;
            white-space: nowrap;
            font-size: 11pt;
        }

        .long-title {
            width: 260px;
            font-size: 11pt;
        }

        .left-align {
            text-align: left;
        }
        
        .left-align .signature-line {
            text-align: center;
        }
        
        .left-align .title-text {
            margin-right: auto;
        }

        .center-align {
            text-align: center;
        }
        
        .center-align .signature-line {
            text-align: center;
        }
        
        .center-align .title-text {
            margin-left: auto;
            margin-right: auto;
        }

        .respectfully-yours {
            text-align: right;
            padding-right: 110px;
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
        <span class="calibri-text" style="font-size:11pt;">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="calibri-text" style="font-size:11pt;">Province of Laguna</span><br>
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
                @php
                    $approvedActivities = is_array($application->approved_activities) 
                        ? $application->approved_activities 
                        : (is_string($application->approved_activities) 
                            ? json_decode($application->approved_activities, true) 
                            : []);
                @endphp
                @if(!empty($approvedActivities))
                    @foreach($approvedActivities as $activity)
                        <tr>
                            <td class="text-left">{{ $activity['title'] ?? '' }}</td>
                            <td>{{ $activity['planned_date'] ?? '' }}</td>
                            <td>{{ $activity['actual_date'] ?? '' }}</td>
                            <td>{{ $activity['proposed_budget'] ?? '' }}</td>
                            <td>{{ $activity['actual_expenditure'] ?? '' }}</td>
                            <td>{{ $activity['target_participants'] ?? '' }}</td>
                            <td>{{ $activity['actual_participants'] ?? '' }}</td>
                            <td>{{ $activity['status'] ?? '' }}</td>
                            <td class="text-left">{{ $activity['justification'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    @for($i = 0; $i < 4; $i++)
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
                    @endfor
                @endif
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
                @php
                    $unapprovedActivities = is_array($application->unapproved_activities) 
                        ? $application->unapproved_activities 
                        : (is_string($application->unapproved_activities) 
                            ? json_decode($application->unapproved_activities, true) 
                            : []);
                @endphp
                @if(!empty($unapprovedActivities))
                    @foreach($unapprovedActivities as $activity)
                        <tr>
                            <td class="text-left">{{ $activity['title'] ?? '' }}</td>
                            <td>{{ $activity['planned_date'] ?? '' }}</td>
                            <td>{{ $activity['actual_date'] ?? '' }}</td>
                            <td>{{ $activity['proposed_budget'] ?? '' }}</td>
                            <td>{{ $activity['actual_expenditure'] ?? '' }}</td>
                            <td>{{ $activity['target_participants'] ?? '' }}</td>
                            <td>{{ $activity['actual_participants'] ?? '' }}</td>
                            <td>{{ $activity['status'] ?? '' }}</td>
                            <td class="text-left">{{ $activity['justification'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    @for($i = 0; $i < 4; $i++)
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
                    @endfor
                @endif
            </tbody>
        </table>

        <div class="signatures-wrapper">
            <div class="section left-align" style="margin-top: 40px;">
                <p>Respectfully yours,</p>
            </div>

            <div class="signature left-align">
                <p style="margin:0 0 1px 0;"><span class="signature-line"><strong>{!! !empty($application->president_name) ? $application->president_name : '&nbsp;' !!}</strong></span></p>
                <p style="text-align:left; margin-top: -12px;"><span class="title-text">Organization President</span></p>
            </div>

            <div class="section left-align" style="margin-top: 30px;">
                <p><strong>NOTED:</strong></p>
            </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="signature left-align" style="flex: 0 0 auto;">
                <p style="margin:0 0 1px 0;"><span class="signature-line" style="min-width:220px;"><strong>{!! !empty($application->adviser_name) ? $application->adviser_name : '&nbsp;' !!}</strong></span></p>
                <p style="margin-top: -12px;"><span class="title-text">Adviser, Student Organization</span></p>
            </div>

            <div style="flex: 0 0 auto; text-align: right; position: relative; top: -60px;">
                <div class="signature right-align" style="display: inline-block;">
                    <p style="margin:0 0 1px 0;"><span class="signature-line" style="min-width:220px;"><strong>{!! !empty($application->dean_name) ? $application->dean_name : '&nbsp;' !!}</strong></span></p>
                    <p style="margin-top: -12px; text-align: right;"><span class="title-text">Dean/Assoc. Dean of College</span></p>
                </div>
            </div>
        </div>            <div class="section center-align" style="margin-top: 30px; margin-bottom: 0;">
                <p style="margin-bottom: 0;"><strong>Recommending Approval:</strong></p>
            </div>
            <div class="signature center-align" style="margin-top: 0;">
                <p style="margin-bottom: 0; margin-top: -10px;"><strong><span class="signature-line" style="min-width:270px;">{!! !empty($application->coordinator_name) ? $application->coordinator_name : '&nbsp;' !!}</span></strong></p>
                <p style="margin-top: -8px; margin-bottom: 0;"><span class="title-text long-title">Coordinator, Student Organization Unit</span></p>
            </div>

            <div class="section center-align" style="margin-top: 30px; margin-bottom: 0;">
                <p style="margin-bottom: 0;"><strong>Approved/Disapproved:</strong></p>
            </div>
            <div class="signature center-align" style="margin-top: 0;">
                <p style="margin-bottom: 0; margin-top: -6px;"><strong><span class="signature-line" style="min-width:380px;">{!! !empty($application->director_name) ? $application->director_name : '&nbsp;' !!}</span></strong></p>
                <p style="margin-top: -8px; margin-bottom: 0;"><span class="title-text long-title">Director/Chairperson, Office of Student Affairs and Services</span></p>
            </div>
        </div>
    </div>

</body>
</html>