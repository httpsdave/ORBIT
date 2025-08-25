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
            padding-left: 60px;
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
                <p class="office-title" style="font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:12px;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
                <div class="cert-title" style="text-align: center; font-size: 15pt; font-weight: bold; margin-top: 13px; margin-bottom: 0;">CERTIFICATION</div>
            </div>

            <!-- Modified date-line section to center DATE under its underline -->
            <div class="date-line">
                <p><u>{{ \Carbon\Carbon::parse($certification->certification_date)->format('F d, Y') }}</u></p>
                    <p style="text-align: right; margin-right: 37px;">Date</p>
            </div>

            <div class="cert-content" style="padding-left: 50px;">
            This certifies that 
            <div style="display: inline-block; vertical-align: bottom; position: relative; top: 6px;">
                <div>
                    <span class="student-blank" style="min-width: 390px; border-bottom: 1px solid black; text-align: center;">{{ $certification->student_name ?? '' }}</span>
                </div>
                <div style="text-align: center; font-size: 11; margin-top: -5px;">
                    <span style="font-size: 10pt;">(LAST NAME, FIRST NAME, MIDDLE INITIAL)</span>
                </div>
            </div>,
            <span style="position: absolute; right: 10px; top: 247px;">   a</span>
            <br>
            </div>
            
            <div style="padding-left: 0px; text-align: left; margin-top: -22px;">
            student taking up 
            <div style="display: inline-block; vertical-align: bottom; position: relative; top: 6px;">
                <div>
                    <span class="course-blank">{{ $certification->course_year_section ?? '' }}</span>
                </div>
                <div style="text-align: center; font-size: 11pt; margin-top: -5px;">
                    <span style="font-size: 10pt; position: relative; top: 5px;">(course, year and section)</span>
                </div>
            </div> from the College of <span class="signature-line" style="min-width:385px; border-bottom: 1px solid black; display: inline-block;margin-top: 20px">{{ $application->college ?? 'Sample Data' }}</span> is a bonafide LSPU Student, not
            <br><br>
            </div>
            
            <div style="padding-left: 0px; text-align: left; margin-top: -35px;">
            <br><br>
            <span style="display: inline-block; margin-top: -15px;word-spacing: 5px">under academic probation, not under disciplinary probation, and the elected/appointed</span>
            <br><br>
            <div style="margin-top: -55px;">
                <span class="signature-line" style="min-width:230px; border-bottom: 1px solid black; display: inline-block;">{{ $certification->position_rank ?? ' ' }}</span> of the <span class="signature-line" style="min-width:315px; border-bottom: 1px solid black; display: inline-block;">{{ $application->organization_name ?? 'Sample Data' }}</span>.
                <br>
                <div style="text-align: center; font-size: 11pt; margin-top: -5px; display: inline-block; width: 120px; margin-left: 45px;">
                    <span style="font-size: 10pt; margin-top: -3px; display: inline-block;">(position/rank)</span>
                </div>
                    <div style="text-align: center; font-size: 11pt; margin-top: -5px; display: inline-block; width: 150px; margin-left: 200px;">
                        <span style="font-size: 10pt; margin-top: -3px; display: inline-block;">(organization)</span>
                </div>
            </div>
        </div>
            
         <!-- Modified signature section with both adviser and dean -->
        <div class="signature-section" style="margin-top: 100px;">
            <!-- Moved "Certified true and correct:" to be just before adviser -->
            <div class="college-is-text" style="padding-left: 25px; margin-bottom: 40px;">
                Certified true and correct:
            </div>
            
            <div class="noted-section" style="padding-left: 65px;">
                <!-- Faculty adviser signature -->
                <div class="faculty-adviser-signature" style="margin-top: -15px; margin-left: -42px;">
                    <div style="text-align: left;">
                        <div class="signature-name-adviser" style="display: inline-block; min-width: 200px; width: auto; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center; margin-left: 0px;">{{ $application->adviser_name ?? 'Sample Data' }}</div>
                        <div class="signature-title-adviser" style="text-align: left; margin-left: 25px;">Organization Adviser(s)</div>
                    </div>
                </div>
                
                <!-- Dean signature right under adviser -->
                <div style="margin-top: 30px; text-align: left; margin-left: -50px;">
                    <div class="signature-name" style="display: inline-block; min-width: 220px; width: auto; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ $application->dean_name ?? 'Sample Data' }}</div>
                    <div class="signature-title" style="text-align: left;margin-left: 25px;">Dean/Assoc. Dean of College</div>
                </div>
                <div style="text-align: center; margin-top: 20px; margin-left: -70px;">Noted:</div>
            </div>
            
            <!-- Director/Chairperson signature section -->
            <div style="margin-top: -65px; text-align: center;">
                <div class="signature-name" style="min-width: 415px; margin-bottom: 0px;">{{ $application->director_name ?? 'Sample Data' }}</div>
                <div class="signature-title" style="margin-top: 2px;">Director/Chairperson, Office of Student Affairs and Services</div>
            </div>
        </div>

            <div class="footer">
            <div class="footer" style="position: absolute; bottom: -5px; width: 100%; height: 20px; line-height: 20px; font-size: 10pt; font-family: Calibri, sans-serif;">
                <div class="footer-left" style="position: absolute; left: .1cm; bottom: -5px;">LSPU-OSAS-SF-006</div>
                <div class="footer-center" style="position: absolute; left: 50%; transform: translateX(-50%); bottom: -5px;">Rev. 1</div>
                <div class="footer-right" style="position: absolute; right: .1cm; bottom: -5px;">09 November 2020</div>
            </div>
        </div>
    @endforeach
</body>
</html>