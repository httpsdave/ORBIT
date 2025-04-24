<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application for Recognition/Renewal of Accredited Student Organization</title>
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
            font-size: 11pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensures the body covers the full viewport height */
        }

        

        .header {
            text-align: center;
            font-size: 15px;
            
            margin: 0 0 0.5cm 0; /* Reduced bottom margin */
            padding-top: 0.5cm; /* Added padding to keep it from the edge */
        }

        .section { 
            margin-bottom: 5px; /* Minimized space between sections */
        }

        .content {
            flex: 1; /* Pushes the footer down */
        }

        .signature { 
            margin-top: 10px; /* Reduced space before signatures */
        }
        
        .signature p {
            margin: 3px 0;
        }
        
        .signature-line {
            display: inline-block;
            min-width: 200px; /* Ensures a baseline width */
            border-bottom: 1px solid black;
            padding-bottom: 2px;
            text-align: center;
        }
        
        .title-text {
            display: block;
            width: 200px;
            text-align: center;
            white-space: nowrap; /* Prevent wrapping to multiple lines */
            font-size: 11pt; /* Slightly smaller font for longer titles */
        }
        
        /* Special handling for longer titles */
        .long-title {
            width: 260px; /* Wider to fit longer text */
            font-size: 11pt; /* Smaller font for very long titles */
        }
        
        .right-align { 
            text-align: right; 
        }
        
        .right-align .signature-line {
            text-align: center;
        }
        
        .right-align .title-text {
            margin-left: auto;
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

        .logo {
            position: absolute;
            top: -0.5cm; /* Adjust as needed */
            left: -2cm; /* Aligns with margin */
            width: 250px; /* Adjust size */
            height: auto;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, sans-serif;
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
        
        /* Preserve the original spacing */
        .original-spacing {
            margin-top: 0;
            margin-bottom: 0;
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

        /* Add this to your CSS */
        .section.center-align.last-section {
            padding-bottom: 0.3cm; /* Reduced from 0.5cm to bring closer to footer */
        }
        .signature.center-align.last-signature {
            margin-bottom: 10px; /* Add a bit of space between signature and footer */
        }

        /* Add this to your CSS */
        .respectfully-yours {
            text-align: right;
            padding-right: 90px; /* Adjust this value to move it more to the left */
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span class="calibri-text">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="calibri-text">Province of Laguna</span><br>
        <br>
       <strong> OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
        <br>
        <span class="sub-header"><strong>APPLICATION FOR RECOGNITION/RENEWAL OF ACCREDITED STUDENT ORGANIZATION</strong></span>
    </div>

    <div class="section right-align">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        <p>Date</p>
    </div>
    
    <div>
        <p><strong>THE DIRECTOR/CHAIRPERSON</strong><br>Office of Student Affairs and Services<br>LSPU</p>
    </div>

    <div class="section justified">
        <p>Sir:</p>
        <p class="indented">I have the honor to apply for recognition/renewal of <u>{{ $application->organization_name }}</u>, a duly recognized organization in this University.</p>
        <p class="indented">In compliance with CHED Memo Order #9 s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII - Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation:</p>
    </div>

    <div class="section list-indented">
        <p>1. Letter for application for recognition (4 copies)</p>
        <p>2. Constitution and By - Laws of the Organization (4 copies)</p>
        <p>3. Program of activities for one (1) year (4 copies)</p>
        <p>4. List of officers with signature, student I.D. Nos. and attached 2x2 I.D. picture (4 copies)</p>
        <p>5. List of members with signature, student I.D. number and attached 1x1 ID picture (4 copies)</p>
        <p>6. Accomplishment report (for renewal of accreditation) (4 copies)</p>
    </div>

    <div class="section justified">
        <p class="indented">It is understood that the provision of the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition is good only for one (1) school year, subject to renewal unless revoked prior to this expiration.</p>
    </div>

    <div class="section respectfully-yours">
        <p>Respectfully yours,</p>
    </div>

    <div class="signature right-align">
        <p><span class="signature-line">{{ $application->president_name }}</span></p>
        <p><span class="title-text">Organization President</span></p>
    </div>

    <div class="signature right-align">
        <p><span class="signature-line">{{ $application->organization_name }}</span></p>
        <p><span class="title-text">Organization Name</span></p>
    </div>

    <div class="section left-align">
        <p>Noted:</p>
    </div>

    <div class="signature left-align">
        <p><span class="signature-line">{{ $application->adviser_name ?? 'N/A' }}</span></p>
        <p><span class="title-text">Adviser, Student Organization</span></p>
    </div>

    <div class="signature right-align">
        <p><span class="signature-line">{{ $application->dean_name ?? 'N/A' }}</span></p>
        <p><span class="title-text">Dean/Assoc. Dean of College</span></p>
    </div>

    <div class="section center-align">
        <p>Recommending Approval:</p>
    </div>

    <div class="signature center-align">
        <p><strong><span class="signature-line">{{ $application->coordinator_name ?? 'N/A' }}</span></strong></p>
        <p><span class="title-text long-title">Coordinator, Student Organization Unit</span></p>
    </div>

    <div class="section center-align last-section">
        <p>Approved/Disapproved:</p>
    </div>

    <div class="signature center-align last-signature">
        <p><strong><span class="signature-line">{{ $application->director_name ?? 'N/A' }}</span></strong></p>
        <p><span class="title-text long-title">Director, Office of Student Affairs and Services</span></p>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-001</div>
        <div class="footer-center">Rev.1</div>
        <div class="footer-right">09 November 2020</div>
    </div>

</body>
</html>