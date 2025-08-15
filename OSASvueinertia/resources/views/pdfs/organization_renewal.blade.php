<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Organization Renewal Form</title>
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

        /* New class for Calibri font */
        .calibri-font {
            font-family: 'Calibri', 'Arial', sans-serif;
        }

        .header {
            text-align: center;
            font-size: 16px;
            
            margin: 0 0 2px 0;
            padding-top: 0.5cm;
        }

        .university-name {
            font-size: 16px;
            font-family: 'Old English Text MT', 'Times New Roman', serif;
            font-weight: bold;
        }

        .office-title {
            font-size: 18px; /* Increase the font size */
            font-weight: bold;
            margin-bottom: 5px; /* Minimal spacing to the next line */
        }

        .form-title {
            font-size: 16px; /* You can adjust this as needed */
            font-weight: bold;
            margin: 0; /* Remove extra margin */
        }


        .section { 
            margin-bottom: 10px;
            position: relative;
        }

        .content {
            flex: 1;
        }

        .signature { 
            margin-top: 15px;
        }

        .signature-line {
            display: inline-block;
            min-width: 250px;
            max-width: 90%;
            border-bottom: 1px solid black;
            margin-bottom: 2px;
            text-align: center;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        .title-under-signature {
            display: inline-block;
            min-width: 250px;
            max-width: 90%;
            text-align: center;
            margin: 0;
            padding: 0;
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

        /* Modified style for "Very respectfully yours" text only */
        .respectfully-text {
            text-align: left;
            margin-left: 59%; /* Positions the text more to the left */
            display: block;
        }

        p { 
            margin: 3px 0;
            word-wrap: break-word;
            line-height: 1.15;
            overflow-wrap: break-word;
        }

        .indented {
            text-indent: 1.27cm;
            margin-bottom: 20px; /* Adjust this value as needed */
        }


        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        .thru-line {
            text-align: left;
            font-style: bold;
            margin: 10px 0;
            margin-left: 70px; /* Adjust this value to move the text to the left */
        }

        .blank-line {
            display: inline-block;
            border-bottom: 1px solid black;
            text-align: center;
            vertical-align: bottom;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        .blank-line-org {
            min-width: 150px;
            max-width: 300px;
        }
        
        .blank-line-college {
            min-width: 100px;
            max-width: 250px;
        }
        
        .blank-line-year {
            min-width: 30px;
            max-width: 40px;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            position: relative;
            padding-bottom: 30px; /* Space for footer */
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: 'Calibri', 'Arial', sans-serif; /* Changed footer font to Calibri */
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
        
        .dynamic-text {
            display: inline;
            word-break: break-word;
        }

        .university-name {
            max-width: 55%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
    <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        
        <p class="calibri-font">Republic of the Philippines</p>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <p class="calibri-font">Province of Laguna</p>
        <br>
        <p class="office-title">Office of Student Affairs and Services</p>
        <p class="form-title">ORGANIZATION RENEWAL FORM</p>
        <br>
    </div>

    <div class="section right-align">
        <p><u>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</u></p>
        <p style="margin-top: 0; text-align: left; width: max-content; padding-left: 540px;">Date</p>
    </div>
    
    <div style="height: 7px;"></div>

    <div class="main-content">
        <div class="section left-align">
            <p><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
            <p>OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
            <p>LSPU</p>
        </div>

        <div class="section">
            <p class="thru-line"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
        </div>

        <div class="section">
            <p>Sir:</p>
            
            <p class="indented">The <span class="dynamic-text"><u>{{ $application->organization_name }}</u></span> wishes to seek renewal of its recognition to function as a Student Organization in the College of <span class="dynamic-text"><u>{{ $application->college }}</u></span> for Academic Year 20<span class="dynamic-text"><u>{{ $application->academic_year_start }}</u></span>-20<span class="dynamic-text"><u>{{ $application->academic_year_end }}</u></span>.</p>
            
            <p class="indented">In this connection, we respectfully request your good office to grant us permission to operate in our institution, subject to the existing rules & regulation of our University.</p>
            <br>
            <p class="indented">Thank you very much.</p>
        </div>

        <div class="section right-align">
            <p class="respectfully-text">Very respectfully yours,</p>

            <div class="signature">
                <p><span class="signature-line">{{ $application->president_name }}</span></p>
                <p><span class="title-under-signature">Organization President</span></p>
            </div>
        </div>

        <div class="section right-align">
            <div class="signature">
                <p><span class="signature-line">{{ $application->organization_name }}</span></p>
                <p><span class="title-under-signature">Name of Organization</span></p>
            </div>
        </div>

        <div class="section left-align">
            <p>Noted:</p>
            <div class="signature">
                <p><span class="signature-line">{{ $application->adviser_name }}</span></p>
                <p><span class="title-under-signature">Adviser's Student Organization</span></p>
            </div>
        </div>

        <div class="section right-align">
            <div class="signature">
                <p><span class="signature-line">{{ $application->dean_name }}</span></p>
                <p><span class="title-under-signature">Dean/Assoc. Dean of College</span></p>
            </div>
        </div>

        <div class="section center-align">
            <p>Recommending Approval:</p>
            <div class="signature">
                <p><span class="signature-line"><strong>{{ $application->coordinator_name }}</strong></span></p>
                <p>Coordinator, Student Organization Unit</p>
            </div>
        </div>

        <div class="section center-align">
            <p>Approved / Disapproved:</p>
            <div class="signature">
                <p><span class="signature-line"><strong>{{ $application->director_name }}</strong></span></p>
                <p>Chairperson, Office of Student Affairs and Services</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-002</div>
        <div class="footer-center">Rev. 1</div>
        <div class="footer-right">09 November 2020</div>
    </div>
</body>
</html>