<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Certification</title>
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
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }

        .header {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 0.5cm 0;
            padding-top: 0.5cm;
        }

        .section {
            margin-bottom: 5px;
        }

        .content {
            flex: 1;
        }

        .signature {
            margin-top: 10px;
        }

        .signature p u {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
        }

        .right-align {
            text-align: right;
        }

        .left-align {
            text-align: left;
        }

        .center-align {
            text-align: center;
        }

        p {
            margin: 3px 0;
            word-wrap: break-word;
            line-height: 1.15;
        }

        .underline {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        .date-line {
            text-align: right;
            margin-bottom: 20px;
        }

        .cert-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 30px 0;
            
        }

        .cert-content {
            text-align: justify;
            margin: 20px 0;
            line-height: 2;
        }

        /* Updated styling for the blank fields */
        .student-blank {
            display: inline-block;
            min-width: 350px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .course-blank {
            display: inline-block;
            min-width: 300px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .position-blank {
            display: inline-block;
            min-width: 250px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        /* Updated checkbox styling to use text-based parentheses */
        .checkbox-item {
            margin: 10px 0;
        }

        .text-checkbox {
            display: inline-block;
            margin-right: 8px;
            font-weight: normal;
        }

        .position-line {
            margin: 10px 0;
        }

        /* Updated signature section to be at bottom center */
        .signature-section {
            position: absolute;
            bottom: 100px; /* Adjust this value as needed to position above footer */
            left: 0;
            right: 0;
            text-align: center;
        }

        .noted-section {
            text-align: left;
            margin-bottom: 20px;
            padding-left: 80px; /* Added padding to move "Noted:" to the left */
        }

        .signature-line {
            margin-top: 40px;
            text-align: center;
        }

        .signature-name {
            margin: 0 auto;
            width: 200px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .signature-title {
            text-align: center;
            font-size: 10pt;
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
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        <br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES
    </div>

    <div class="date-line">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        <p>DATE</p>
    </div>

    <div class="cert-title">
        CERTIFICATION
    </div>

    <div class="cert-content">
        This certifies that <span class="student-blank">{{ $application->student_name ?? '' }}</span>, a 
        <span class="course-blank">{{ $application->course_year_section ?? '' }}</span>, 
        
        student of this College is:
    </div>

    <!-- Text-based checkboxes -->
    <div class="checkbox-item">
        <span class="text-checkbox">({{ $application->is_bonafide ? '/' : ' ' }})</span> a bonafide student;
    </div>
    <div class="checkbox-item">
        <span class="text-checkbox">({{ $application->is_not_academic_probation ? '/' : ' ' }})</span> not under academic probation;
    </div>
    <div class="checkbox-item">
        <span class="text-checkbox">({{ $application->is_not_disciplinary_probation ? '/' : ' ' }})</span> not under disciplinary probation;
    </div>
    <div class="position-line">
        <span class="text-checkbox">({{ $application->has_position ? '/' : ' ' }})</span> position/rank in the organization <span class="position-blank">{{ $application->position_rank ?? '' }}</span>;
    </div>

    <!-- Moved signature section to the bottom -->
    <div class="signature-section">
        <div class="noted-section">
            <p>Noted:</p>
        </div>

        <div class="signature-line">
            <div class="signature-name">{{ $application->adviser_name ?? '' }}</div>
            <div class="signature-title">Faculty Adviser(s)</div>
        </div>

        <div class="signature-line">
            <div class="signature-name">{{ $application->dean_name ?? '' }}</div>
            <div class="signature-title">Dean/Assoc. Dean of College</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-006</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>
</body>
</html>