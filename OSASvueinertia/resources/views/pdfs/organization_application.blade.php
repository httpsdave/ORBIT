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
    margin: 2.54cm; /* Sets all margins (top, bottom, left, right) to 1 inch */
}

body {
    font-family: 'Times New Roman', serif;
    font-size: 10pt; /* Slightly reduced font size for better fit */
    line-height: 1.1; /* Adjusted line spacing to save space */
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    max-width: calc(210mm - 5.08cm); /* A4 width (210mm) minus 1-inch (2.54cm) margins on both sides */
}

.header { 
    text-align: center; 
    font-size: 16px; /* Slightly reduced font size for compactness */
    font-weight: bold; 
    margin: 0 0 5px 0; /* Reduced bottom margin */
}

.section { 
    margin-bottom: 5px; /* Minimized space between sections */
}

.content { 
    text-align: justify; 
}

.signature { 
    margin-top: 15px; /* Reduced space before signatures */
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
    margin: 3px 0; /* Further minimized paragraph spacing */
    word-wrap: break-word; 
}


    </style>
</head>
<body>

    <div class="header">
        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES
    </div>

    <div class="section right-align">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        
        <p>Date</p>
    </div>

    <div class="section">
        <p>The Director/Chairperson<br>Office of Student Affairs and Services<br>LSPU</p>
    </div>

    <div class="section content">
        <p>Sir:</p>
        <p>I have the honor to apply for recognition/renewal of {{ $application->organization_name }}, a duly recognized organization in this University.</p>
        <p>In compliance with CHED Memo Order #9 s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII - Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation:</p>
    </div>

    <div class="section">
        <p>1. Letter for application for recognition (4 copies)</p>
        <p>2. Constitution and By - Laws of the Organization (4 copies)</p>
        <p>3. Program of activities for one (1) year (4 copies)</p>
        <p>4. List of officers with signature, student I.D. Nos. and attached 2x2 I.D. picture (4 copies)</p>
        <p>5. List of members with signature, student I.D. number and attached 1x1 ID picture (4 copies)</p>
        <p>6. Accomplishment report (for renewal of accreditation) (4 copies)</p>
    </div>

    <div class="section content">
        <p>It is understood that the provision of the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition is good only for one (1) school year, subject to renewal unless revoked prior to this expiration.</p>
    </div>

    <div class="section right-align">
        <p>Respectfully yours,</p>
    </div>

    <div class="signature right-align">
        <p><u>{{ $application->president_name }}</u></p>
        
        <p>Organization President</p>
    </div>

    <div class="signature right-align">
        <p><u>{{ $application->organization_name }}</u></p>
        
        <p>Organization Name</p>
    </div>

    <div class="section left-align">
        <p>Noted:</p>
    </div>

    <div class="signature left-align">
        <p><u>{{ $application->adviser_name ?? 'N/A' }}</u></p>
        
        <p>Adviser, Student Organization</p>
    </div>

    <div class="signature right-align">
        <p><u>{{ $application->dean_name ?? 'N/A' }}</u></p>
        
        <p>Dean/Assoc. Dean of College</p>
    </div>

    <div class="section center-align">
        <p>Recommending Approval:</p>
    </div>

    <div class="signature center-align">
        <p><u>{{ $application->coordinator_name ?? 'N/A' }}</u></p>
        
        <p>Coordinator, Student Organization Unit</p>
    </div>

    <div class="section center-align">
        <p>Approved/Disapproved:</p>
    </div>

    <div class="signature center-align">
        <p><u>{{ $application->director_name ?? 'N/A' }}</u></p>
        
        <p>Director, Office of Student Affairs and Services</p>
    </div>

    <div class="section center-align">
        <p>LSPU-OSAS-SF-001 Rev.1 09 November 2020</p>
    </div>

</body>
</html>
