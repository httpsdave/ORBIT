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
    font-size: 10pt;
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
    font-size: 16px;
    font-weight: bold;
    margin: 0 0 2px 0; /* Reduced bottom margin */
    padding-top: 0.5cm; /* Added padding to keep it from the edge */
}

.section { 
    margin-bottom: 5px; /* Minimized space between sections */
}

.content {
    flex: 1; /* Pushes the footer down */
}

.signature { 
    margin-top: 15px; /* Reduced space before signatures */
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
    top: 0.5cm; /* Adjust as needed */
    left: 2.54cm; /* Aligns with margin */
    width: 70px; /* Adjust size */
    height: auto;
}

.footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px; /* Fixed height to ensure alignment */
            line-height: 20px; /* Vertically center text */
            font-size: 10pt;
        }

        .footer-left {
            position: absolute;
            left: 2.54cm;
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
            right: 2.54cm;
            bottom: 0;
        }



    </style>
</head>
<body>

    <div class="header">
    <img src="{{ url('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">


        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES<br>
        <br>
        <span class="sub-header">APPLICATION FOR RECOGNITION/RENEWAL OF ACCREDITED STUDENT ORGANIZATION</span>
    </div>

    <div class="section right-align">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        
        <p>Date</p>
    </div>
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

    <div class="section right-align">
        <p>Respectfully yours,</p>
    </div>

    <div class="signature right-align">
    <p><span class="underline">{{ $application->president_name }}</span></p>
        
        <p>Organization President</p>
    </div>

    <div class="signature right-align">
    <p><span class="underline">{{ $application->organization_name }}</span></p>
        
        <p>Organization Name</p>
    </div>

    <div class="section left-align">
        <p>Noted:</p>
    </div>

    <div class="signature left-align">
    <p><span class="underline">{{ $application->adviser_name ?? 'N/A' }}</span></p>
        
        <p>Adviser, Student Organization</p>
    </div>

    <div class="signature right-align">
    <p><span class="underline">{{ $application->dean_name ?? 'N/A' }}</span></p>
        
        <p>Dean/Assoc. Dean of College</p>
    </div>

    <div class="section center-align">
        <p>Recommending Approval:</p>
    </div>

    <div class="signature center-align">
    <p><span class="underline">{{ $application->coordinator_name ?? 'N/A' }}</span></p>
        
        <p>Coordinator, Student Organization Unit</p>
    </div>

    <div class="section center-align">
        <p>Approved/Disapproved:</p>
    </div>

    <div class="signature center-align">
    <p><span class="underline">{{ $application->director_name ?? 'N/A' }}</span></p>
        
        <p>Director, Office of Student Affairs and Services</p>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-001</div>
        <div class="footer-center">Rev.1</div>
        <div class="footer-right">09 November 2020</div>
    </div>

</body>
</html>
