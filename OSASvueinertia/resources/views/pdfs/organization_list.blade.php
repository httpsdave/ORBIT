<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Members - Student Organization</title>
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
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 0.5cm 0;
            padding-top: 0.5cm;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        /* Table spacing adjustments */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px; /* Reduced spacing between rows */
        }

        td {
            vertical-align: top;
            padding: 0;
            width: 50%;
        }

        .member-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px; /* Add space between member entries */
        }

        .photo-cell {
            width: 70px;
            vertical-align: top;
            padding-right: 10px;
        }

        .info-cell {
            vertical-align: top;
            width: calc(100% - 80px); /* Ensure info cell has proper width */
        }

        .photo-box {
            border: 1px solid black;
            width: 70px;
            height: 70px;
            text-align: center;
            line-height: 70px;
            font-size: 10pt;
            position: relative;
            overflow: hidden;
        }
        
        /* Ensure "PICTURE" text stays inside the box */
        .photo-box-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
            font-size: 10pt;
        }

        /* Remove underlines and adjust spacing */
        .member-info {
            margin-bottom: 5px;
            min-height: 16px; /* Reduced height */
            padding: 2px 0;
            white-space: nowrap; /* Prevent text wrapping */
            overflow: hidden;    /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis for overflow */
            max-width: 100%;     /* Ensure text doesn't exceed width */
            display: block;      /* Make it a block element */
        }

        .footer {
            position: fixed;
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

        .signature {
            margin-top: 20px;
        }

        .signature p {
            margin: 3px 0;
        }

        .signature-line {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
        }

        .center-align {
            text-align: center;
        }

        .right-align {
            text-align: right;
        }

        .left-align {
            text-align: left;
        }
        
        .semester-section {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        <br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES<br>
        <br>
        <span class="sub-header">List of Members</span>
    </div>

    <div class="semester-section">
        <span>{{ $application->semester ?? '__' }} Sem. / AY {{ $application->academic_year_start ?? '20__' }}-{{ $application->academic_year_end ?? '20__' }}</span>
    </div>

    <div class="section center-align">
        <p>Name of Organization: <span class="signature-line">{{ $application->organization_name }}</span></p>
    </div>

    <!-- Member grid using simple tables -->
    <table>
        @php
            // Calculate how many rows we need
            $rowCount = ceil($members->count() / 2);
            // Create array of members for easier access
            $membersArray = $members->toArray();
        @endphp
        
        @for ($row = 0; $row < $rowCount; $row++)
            <tr>
                @for ($col = 0; $col < 2; $col++)
                    <td>
                        @php
                            $index = $row * 2 + $col;
                            $member = $members[$index] ?? null;
                        @endphp
                        
                        @if ($member)
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box">
                                            @if($member->photo_path)
                                                <img src="{{ storage_path('app/public/' . $member->photo_path) }}" alt="Member Photo" width="68" height="68">
                                            @else
                                                <span class="photo-box-text">1 x 1</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info">{{ $member->student_name ?? '' }}</div>
                                        <div class="member-info">{{ $member->student_number ?? '' }}</div>
                                        <div class="member-info">{{ $member->course_year_section ?? '' }}</div>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box"><span class="photo-box-text">1 x 1</span></div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info"></div>
                                        <div class="member-info">Student Number</div>
                                        <div class="member-info">Course - Year Section</div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                @endfor
            </tr>
        @endfor
    </table>

    <div class="signature left-align">
        <p><span class="signature-line">{{ $application->adviser_name ?? '' }}</span></p>
        <p>Faculty Adviser</p>
        <p>Date: <span class="signature-line">{{ now()->format('F d, Y') }}</span></p>
    </div>

    <div class="signature right-align">
        <p><span class="signature-line">{{ $application->second_adviser ?? '' }}</span></p>
        <p>Faculty Adviser</p>
        <p>Date: <span class="signature-line">{{ now()->format('F d, Y') }}</span></p>
    </div>

    <div class="section center-align">
        <p>Noted:</p>
    </div>

    <div class="signature center-align">
        <p><span class="signature-line">{{ $application->dean_name ?? '' }}</span></p>
        <p>Dean/Assoc. Dean of College</p>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-005</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>
</body>
</html>