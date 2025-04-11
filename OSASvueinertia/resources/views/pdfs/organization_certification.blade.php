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

        .checkbox-item {
            margin: 10px 0;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            margin-right: 8px;
        }

        .position-line {
            margin: 10px 0;
        }

        .noted-section {
            margin-top: 30px;
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
        This certifies that <u>{{ $application->student_name ?? '___________________________________' }}</u>, a 
        <u>{{ $application->course_year_section ?? '___________________________________' }}</u>, 
        
        student of this College is:
    </div>

    <div class="checkbox-item">
        <span class="checkbox">( )</span> a bonafide student;
    </div>
    <div class="checkbox-item">
        <span class="checkbox">( )</span> not under academic probation;
    </div>
    <div class="checkbox-item">
        <span class="checkbox">( )</span> not under disciplinary probation;
    </div>
    <div class="position-line">
        <span class="checkbox">( )</span> position/rank in the organization <u>{{ $application->position_rank ?? '___________________' }}</u>;
    </div>

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

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-006</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>
</body>
</html>