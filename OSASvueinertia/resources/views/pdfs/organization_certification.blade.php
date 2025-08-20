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
           
            margin: 0 0 0.5cm 0;
            padding-top: 0.5cm;
        }
        
        /* Updated header elements with Calibri font */
        .header-title {
            /* Republic of the Philippines */
            font-size: 16px;
            font-family: 'Calibri', sans-serif;
        }
        
        .header-university {
            /* University name */
            font-size: 18px;
        }
        
        .header-province {
            /* Province of Laguna */
            font-size: 16px;
            font-family: 'Calibri', sans-serif;
        }
        
        .header-office {
            /* Office of Student Affairs and Services */
            font-size: 18px;
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
            min-width: 350px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .position-blank {
            display: inline-block;
            min-width: 210px;
            border-bottom: 1px solid black;
            text-align: center;
        }

        /* Updated checkbox styling to use text-based parentheses */
        /* Modified: Added 1.5 line spacing and consistent padding */
        .checkbox-item {
            margin: 15px 0; /* Increased margin for more spacing */
            padding-left: 40px;
            padding-right: 40px;
            line-height: 1.5; /* Added 1.5 line spacing */
        }

        .text-checkbox {
            display: inline;
            margin-right: 2px;
            font-weight: normal;
        }

        /* Modified: Added 1.5 line spacing to position line */
        .position-line {
            margin: 15px 0; /* Increased margin for more spacing */
            padding-left: 40px;
            padding-right: 40px;
            line-height: 1.5; /* Added 1.5 line spacing */
        }

        /* Added new style for the standalone text */
        .college-is-text {
            margin: 45px 0 25px 0; /* Add space above and below */
            padding-left: 40px;
            padding-right: 40px;
            line-height: 1.5;
        }
        
        /* NEW: Added margin-top to the checkbox-container to move it down */
        .checkbox-container {
            margin-top: 40px; /* Adjust this value to move checkboxes further down */
        }

        /* MODIFIED: Adjusted the signature section */
        .signature-section {
            position: relative;
            /* THIS CONTROLS THE DISTANCE FROM THE CHECKBOXES TO THE "NOTED:" SECTION */
            margin-top: 200px; /* ADJUST THIS VALUE to move the "Noted:" section up/down - reduced to account for new spacing */
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

        /* Updated footer with Calibri font */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: 'Calibri', sans-serif;
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
        .university-name {
            max-width: 55%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }

        /* Page break for multiple certifications */
        .page-break {
            page-break-before: always;
        }

        .certification-page {
            min-height: 100vh;
            position: relative;
        }
    </style>
</head>
<body>
    @foreach($studentCertifications as $index => $certification)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="certification-page">
            <div class="header">
                <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
                <div class="header-title">Republic of the Philippines</div>
                <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
                <div class="header-province">Province of Laguna</div>
                <div class="header-office"><strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong></div>
            </div>

            <!-- Modified date-line section to center DATE under its underline -->
            <div class="date-line">
                <p><u>{{ \Carbon\Carbon::parse($certification->certification_date)->format('F d, Y') }}</u></p>
                <p style="text-align: right; margin-right: 15px;">DATE</p>
            </div>

            <div class="cert-title">
                CERTIFICATION
            </div>

            <div class="cert-content">
            This certifies that 
            <div style="display: inline-block; vertical-align: bottom; position: relative; top: 6px;">
                <div>
                    <span class="student-blank">{{ $certification->student_name ?? '' }}</span>
                </div>
                <div style="text-align: center; font-size: 11; margin-top: -5px;">
                    (LAST NAME, FIRST NAME, MIDDLE INITIAL)
                </div>
            </div>, a 
            <div style="display: inline-block; vertical-align: bottom; position: relative; top: 6px;">
                <div>
                    <span class="course-blank">{{ $certification->course_year_section ?? '' }}</span>
                </div>
                <div style="text-align: center; font-size: 11pt; margin-top: -5px;">
                    (course, year and section)
                </div>
            </div>.
        </div>
            
            <!-- Moved "student of this College is:" to its own line -->
            <div class="college-is-text">
                student of this College is:
            </div>

           <!-- Wrapped checkboxes in a container with margin-top -->
        <div class="checkbox-container" style="padding-left: 25px;">
            <!-- Text-based checkboxes with increased spacing -->
            <div class="checkbox-item">
                <span class="text-checkbox">({{ $certification->is_bonafide ? '/' : ' ' }})</span> a bonafide student;
            </div>
            <div class="checkbox-item">
                <span class="text-checkbox">({{ $certification->is_not_academic_probation ? '/' : ' ' }})</span> not under academic probation;
            </div>
            <div class="checkbox-item">
                <span class="text-checkbox">({{ $certification->is_not_disciplinary_probation ? '/' : ' ' }})</span> not under disciplinary probation;
            </div>
            <div class="position-line">
                <span class="text-checkbox">({{ $certification->has_position ? '/' : ' ' }})</span> position/rank in the organization <span class="position-blank">{{ $certification->position_rank ?? '' }}</span>;
            </div>
        </div>

         <!-- Modified Noted section to move just the "Noted:" text higher -->
        <div class="signature-section" style="margin-top: 100px;">
            <div class="noted-section" style="padding-left: 80px;">
                <p style="margin-left: -40px; margin-bottom: 70px;"><strong>Noted:</strong></p>
                <!-- Faculty adviser signature -->
                <div class="faculty-adviser-signature" style="margin-top: -15px;">
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
        </div>
    @endforeach
</body>
</html>