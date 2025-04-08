<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commitment Form</title>
    <style>
        /* Set A4 paper size for print */
        @page {
            size: A4;
            margin-top: 0.5cm; /* Reduced top margin to bring header closer */
            margin-bottom: 1.0cm; /* Reduced bottom margin to bring footer closer */
            margin-left: 2.54cm; /* Standard 1-inch margin */
            margin-right: 2.54cm; /* Standard 1-inch margin */
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
            min-height: 100vh; /* Ensures the body covers the full viewport height */
            position: relative; /* For absolute positioning of children */
        }

        .header {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin: 0 0 0.5cm 0; /* Reduced bottom margin */
            padding-top: 0.5cm; /* Added padding to keep it from the edge */
        }

        .section { 
            margin-bottom: 5px; /* Minimized space between sections */
        }

        .content {
            flex: 1; /* Pushes the footer down */
            margin-bottom: 150px; /* Add space for the bottom sections */
        }

        .signature { 
            margin-top: 10px; /* Reduced space before signatures */
        }
        
        .signature p u {
            display: inline-block;
            min-width: 200px; /* Ensures a baseline width */
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

        .section.center-align {
            margin-top: 5px; /* Reduce spacing before footer */
            padding-bottom: 0.5cm; /* Space before footer to keep it readable */
        }
        
        p { 
            margin: 3px 0; /* Minimizes paragraph spacing */
            word-wrap: break-word;
            line-height: 1.15; /* Ensures single spacing */
        }
        
        .indented {
            text-indent: 1.27cm; /* Adjust the indent size as needed */
        }
        
        .list-indented p {
            text-indent: 0; /* Remove default text indent */
            padding-left: 1cm; /* Adjust as needed */
        }
        
        .justified {
            text-align: justify; /* Justify text alignment */
        }
        
        .underline {
            display: inline-block;
            min-width: 200px; /* Ensures a baseline width */
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: -0.5cm; /* Adjust as needed */
            left: -2cm; /* Aligns with margin */
            width: 250px; /* Adjust size */
            height: auto;
        }

        /* Bottom sections for approval signatures */
        .bottom-sections {
            position: absolute;
            bottom: 40px; /* Position above the footer */
            left: 0;
            right: 0;
            text-align: center;
        }

        .approval-section {
            margin-bottom: 15px;
        }

        /* Document footer with form numbers */
        .doc-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px; /* Fixed height to ensure alignment */
            line-height: 20px; /* Vertically center text */
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

        .form-field {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            text-align: center;
            margin: 0 5px;
        }
        
        /* Updated styles for the noted section */
        .noted-section {
            position: absolute;
            bottom: 240px; /* Position higher above the recommending approval section */
            left: 40px; /* A little more to the right */
            text-align: left;
            width: 50%;
        }
        
        /* Updated styles for signature section to be partially right-aligned */
        .signature-section {
            text-align: right;
            margin-top: 20px;
            padding-right: 40px; /* Add padding from the right to move content slightly left */
        }
        
        .signature-section p {
            margin: 3px 0;
        }
        
        /* Added styles for better spacing between specific sections */
        .address-block {
            margin-bottom: 15px; /* Increased space after address block */
        }
        
        .thru-line {
            margin-bottom: 20px; /* Increased space after "Thru:" line */
            padding-left: 1.27cm; /* Added indent to match other indented paragraphs */
            text-indent: 0; /* Ensures text starts at the padding position */
        }
        
        .sir-greeting {
            margin-top: 10px; /* Added space before "Sir," */
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
        <strong>Office of Student Affairs and Services</strong><br>
        <br>
        <span class="sub-header"><strong>COMMITMENT FORM</strong></span>
    </div>

    <div class="content">
        <div class="section">
            <p class="address-block"><strong>THE DIRECTOR/CHAIRPERSON</strong><br>
            <strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
            <strong>LSPU</strong></p>
            
            <p class="thru-line"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
        </div>

        <div class="section justified">
            <p class="sir-greeting">Sir,</p>
            <p class="indented">This letter is in connection with the application for recognition of 
            <u>{{ $application->organization_name ?? '________________' }}</u> as an LSPU Student Organization.</p>
            <p class="indented">I, the undersigned, have committed to serve as the organizations Faculty 
            Adviser for the academic year 20<u>{{ $application->academic_year_start ?? '__' }}</u>-20<u>{{ $application->academic_year_end ?? '__' }}</u>, and will therefore assume full responsibility as 
            provided in the guidelines for the recognition of student organizations.</p>
            <p class="indented">Furthermore, I certify to the correctness and completeness of the documents 
            attached to the organization application for recognition.</p>
        </div>

        <!-- Right-aligned signature section -->
        <div class="signature-section">
            <p><strong>Very respectfully yours,</strong></p>
            <p>Name: <span class="underline">{{ $application->adviser_name ?? '_______________________________' }}</span></p>
            <p>Signature: <span class="underline">{{ $application->adviser_signature ?? '' }}</span></p>
            <p>College: <span class="underline">{{ $application->adviser_college ?? '_______________________________' }}</span></p>
            <p>Academic Rank: <span class="underline">{{ $application->adviser_rank ?? '_______________________________' }}</span></p>
            <p>Home Address: <span class="underline">{{ $application->adviser_address ?? '_______________________________' }}</span></p>
            <p>Contact Number(s): <span class="underline">{{ $application->adviser_contact ?? '_______________________________' }}</span></p>
            <p>Date: <span class="underline">{{ \Carbon\Carbon::parse($application->form_date)->format('F d, Y') ?? '_______________________________' }}</span></p>
        </div>
    </div>

    <!-- Noted section repositioned higher and more to the right -->
    <div class="noted-section">
        <p>Noted:</p>
        <p><span class="underline">{{ $application->dean_name ?? '_______________________________' }}</span></p>
        <p>Dean/Assoc. Dean of College</p>
    </div>

    <!-- Bottom sections positioned at bottom center -->
    <div class="bottom-sections">
        <div class="approval-section">
            <p>Recommending Approval:</p>
            <p><span class="underline">{{ $application->coordinator_name ?? '_______________________________' }}</span></p>
            <p>Coordinator, Student Organization Unit</p>
        </div>

        <div class="approval-section">
            <p>Approved / Disapproved:</p>
            <p><span class="underline">{{ $application->director_name ?? '_______________________________' }}</span></p>
            <p>Director, Office of Student Affairs and Services</p>
        </div>
    </div>

    <div class="doc-footer">
        <div class="footer-left">LSPU-OSAS-SF-003</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>

</body>
</html>