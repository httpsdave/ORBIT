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
            /* Increased base font size from 15px to 16px */
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 0.5cm 0;
            padding-top: 0.5cm;
        }
        
        /* New styles for specific header elements */
        .header-title {
            /* Republic of the Philippines */
            font-size: 16px;
        }
        
        .header-university {
            /* University name */
            font-size: 18px;
        }
        
        .header-province {
            /* Province of Laguna */
            font-size: 16px;
        }
        
        .header-office {
            /* Office of Student Affairs and Services */
            font-size: 20px;
            margin-top: 40px;
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
            font-size: 20pt;
            font-weight: bold;
            margin: 30px 0;
            
        }

        /* Modified: Updated line height to 1.5 for 1.5 spacing */
        .cert-content {
            text-align: justify;
            margin: 20px 0;
            line-height: 1.5; /* Changed from 2 to 1.5 for 1.5 spacing */
            padding-left: 40px;
            padding-right: 40px;
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
        /* Modified: Added 1.5 line spacing and consistent padding */
        .checkbox-item {
            margin: 10px 0;
            padding-left: 40px;
            padding-right: 40px;
            line-height: 1.5; /* Added 1.5 line spacing */
        }

        .text-checkbox {
            display: inline-block;
            margin-right: 8px;
            font-weight: normal;
        }

        /* Modified: Added 1.5 line spacing to position line */
        .position-line {
            margin: 10px 0;
            padding-left: 40px;
            padding-right: 40px;
            line-height: 1.5; /* Added 1.5 line spacing */
        }

        /* MODIFIED: Adjusted the signature section */
        .signature-section {
            position: relative;
            /* THIS CONTROLS THE DISTANCE FROM THE CHECKBOXES TO THE "NOTED:" SECTION */
            margin-top: 250px; /* ADJUST THIS VALUE to move the "Noted:" section up/down */
        }

        /* MODIFIED: Adjusted the noted section positioning */
        .noted-section {
            text-align: left;
            /* THIS CONTROLS THE DISTANCE BETWEEN "NOTED:" AND THE DEAN SIGNATURE */
            margin-bottom: 100px; /* ADJUST THIS VALUE to control space between adviser and dean */
            padding-left: 80px;
        }

        /* Dean signature section positioned at bottom */
        .dean-signature-section {
            position: absolute;
            /* THIS CONTROLS HOW HIGH UP FROM THE BOTTOM THE DEAN SIGNATURE APPEARS */
            bottom: 100px; /* ADJUST THIS VALUE to move the dean signature up/down */
            left: 0;
            right: 0;
            text-align: center;
        }

        .adviser-section {
            margin-top: 10px;
            padding-left: 80px;
        }

        .signature-line {
            margin-top: 40px;
            text-align: center;
        }

        /* Modified: Added different signature names for advisor and dean */
        .signature-name-adviser {
            margin: 0;
            width: 200px;
            border-bottom: 1px solid black;
            text-align: center;
            margin-left: -40px;
        }

        .signature-name {
            margin: 0 auto;
            width: 200px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .signature-title {
            text-align: center;
            font-size: 12pt;
        }

        /* Modified: Added a new class for adviser title */
        .signature-title-adviser {
            text-align: left;
            font-size: 12pt;
            margin-left: 10px;
        }

        /* MODIFIED: Faculty adviser signature spacing */
        .faculty-adviser-signature {
            margin-top: 30px; /* ADJUST THIS VALUE to control space between "Noted:" and signature line */
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
        
        .labeled-blank {
        position: relative;
        display: inline-block;
        }
        
        .student-blank, .course-blank {
            position: relative;
            z-index: 1;
        }
        
        .blank-label {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 10pt;
            line-height: 1;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <div class="header-title">Republic of the Philippines</div>
        <div class="header-university">Laguna State Polytechnic University</div>
        <div class="header-province">Province of Laguna</div>
        <div class="header-office">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
    </div>

    <div class="date-line">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        <p>DATE</p>
    </div>

    <div class="cert-title">
        CERTIFICATION
    </div>

    <!-- Modified cert-content section with simpler approach -->
    <div class="cert-content">
        This certifies that 
        <div style="display: inline-block; vertical-align: top;">
            <div>
                <span class="student-blank">{{ $application->student_name ?? '' }}</span>
            </div>
            <div style="text-align: center; font-size: 10pt; margin-top: -5px;">
                student name
            </div>
        </div>, a 
        <div style="display: inline-block; vertical-align: top;">
            <div>
                <span class="course-blank">{{ $application->course_year_section ?? '' }}</span>
            </div>
            <div style="text-align: center; font-size: 10pt; margin-top: -5px;">
                course/year and section
            </div>
        </div>, 
        
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

    <!-- MODIFIED: Split into two separate sections -->
    <div class="signature-section">
        <div class="noted-section">
            <p>Noted:</p>
            <!-- Faculty adviser signature -->
            <div class="faculty-adviser-signature">
                <div class="signature-name-adviser">{{ $application->adviser_name ?? '' }}</div>
                <div class="signature-title-adviser">Faculty Adviser(s)</div>
            </div>
        </div>
    </div>

    <!-- MODIFIED: Separate dean signature section to keep at bottom -->
    <div class="dean-signature-section">
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