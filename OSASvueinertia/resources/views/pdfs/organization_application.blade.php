<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Application</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; font-size: 18px; font-weight: bold; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; }
        .signature { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        Republic of the Philippines<br>
        Laguna State Polytechnic University<br>
        Province of Laguna<br>
        OFFICE OF STUDENT AFFAIRS AND SERVICES
    </div>

    <div class="section">
        <p>Date: <strong>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</strong></p>
        <p>The Director/Chairperson<br>Office of Student Affairs and Services<br>LSPU</p>
    </div>

    <div class="section">
        <p>Sir/Madam,</p>
        <p>I have the honor to apply for recognition/renewal of <strong>{{ $application->organization_name }}</strong>, a duly recognized student organization in this University.</p>
    </div>

    <div class="section">
        <p> In compliance with CHED Memo Order #9 s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII - Student Development, Section 19. Student Organizations and Activities) I am submitting for proper action the following requirements for recognition and accreditation. </p>
    </div>

    <div class="section">
        <p>1.	Letter for application for recognition (4 copies) </p>
        <p>2.	Constitution and By - Laws of the Organization (4 copies) </p>
        <p>3.	Program of activities for one (1) year (4 copies) </p>
        <p>4.	List of officers with signature, student I.D. Nos. and attached 2x2 1.D. picture (4 copies)</p>
        <p>5.	List of members with signature, student I.D. number and attached 1x1 ID picture (4 copies) </p>
        <p>6.	Accomplishment report (for renewal of accreditation) (4 copies)  </p>
    </div>

    <div class="section">
        <p> It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition are good only for one (1) school year subject to renewal unless revoked prior to this expiration. </p>
    </div>

    <div class="section">
        <p><strong>President:</strong> {{ $application->president_name }}</p>
        <p><strong>Adviser:</strong> {{ $application->adviser_name ?? 'N/A' }}</p>
        <p><strong>Dean:</strong> {{ $application->dean_name ?? 'N/A' }}</p>
        <p><strong>Coordinator:</strong> {{ $application->coordinator_name ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ $application->status }}</p>
    </div>

    <div class="signature">
        <p>________________________</p>
        <p>Organization President</p>
    </div>

    <div class="signature">
        <p>________________________</p>
        <p>Adviser, Student Organization</p>
    </div>

    <div class="signature">
        
        <p>________________________</p>
        <p>Dean/Associate Dean</p>
    </div>

    <div class="signature">
        <p>________________________</p>
        <p>Coordinator, Student Organization Unit</p>
    </div>

    <div class="signature">
        <p>________________________</p>
        <p>Director, Office of Student Affairs and Services</p>
    </div>
</body>
</html>

-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application for Recognition/Renewal of Accredited Student Organization</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .content { text-align: justify; }
        .signature { margin-top: 50px; }
        .right-align { text-align: right; }
        .left-align { text-align: left; }
        .center-align { text-align: center; }
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
    <p><strong>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</strong></p>
        <p>____________________</p>
        <p>Date</p>
    </div>

    <div class="section">
        <p>The Director/Chairperson<br>Office of Student Affairs and Services<br>LSPU</p>
    </div>

    <div class="section content">
        <p>Sir:</p>
        <p>I have the honor to apply for recognition/renewal of <strong>{{ $application->organization_name }}</strong>, a duly recognized organization in this University.</p>
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
    <p><strong>{{ $application->president_name }}</strong></p>
        <p>________________________</p>
        <p>Organization President</p>
    </div>

    <div class="signature right-align">
        <p>{{ $application->organization_name }}</p>
        <p>________________________</p>
        <p>Organization Name</p>
        
    </div>

    <div class="section left-align">
        <p><strong>Noted:</strong></p>
    </div>

    <div class="signature left-align">
    <p><strong>{{ $application->adviser_name ?? 'N/A' }}</strong></p>
        <p>________________________</p>
        <p>Adviser, Student Organization</p>
    </div>

    <div class="signature right-align">
        <p><strong>{{ $application->dean_name ?? 'N/A' }}</strong></p>
        <p>________________________</p>
        <p>Dean/Assoc. Dean of College</p>
    </div>

    <div class="section center-align">
        <p><strong>Recommending Approval:</strong></p>
    </div>

    <div class="signature center-align">
    <p><strong>{{ $application->coordinator_name ?? 'N/A' }}</strong></p>
        <p>___________________________</p>
        <p>Coordinator, Student Organization Unit</p>
    </div>

    <div class="section center-align">
        <p><strong>Approved/Disapproved:</strong></p>
    </div>

    <div class="signature center-align">
    <p><strong>{{ $application->director_name ?? 'N/A' }}</strong></p>
        <p>________________________________</p>
        <p>Director, Office of Student Affairs and Services</p>
    </div>

    <div class="section center-align">
        <p>LSPU-OSAS-SF-001 Rev.1 09 November 2020</p>
    </div>

</body>
</html>
