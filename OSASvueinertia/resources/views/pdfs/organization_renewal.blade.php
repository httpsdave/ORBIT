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
            font-family: Calibri, sans-serif;
        }

        .header {
            text-align: center;
            font-size: 11pt;
            
            margin: 0 0 2px 0;
            padding-top: 0.5cm;
        }

        .university-name {
            max-width: 55%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
            font-family: 'Old English Text MT', 'Times New Roman', serif;
            font-weight: bold;
        }

        .office-title {
            font-size: 16px; /* Match ORGANIZATION RENEWAL FORM font size */
            font-weight: bold;
            margin-top: 0px; /* Reduce spacing above by 10px total */
            margin-bottom: 10px; /* Add 5px more spacing below */
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
            width: fit-content;
            border-bottom: 1px solid black;
            margin-bottom: 2px;
            text-align: center;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        .title-under-signature {
            display: inline-block;
            width: fit-content;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .title-left-adjust {
            transform: translateX(-5px);
        }

        .title-left-adjust-more {
            transform: translateX(-10px);
        }

        .signature-line-inline {
            vertical-align: text-bottom !important;
            position: relative;
            top: 0px;
        }

        .title-right-adjust {
            transform: translateX(10px);
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
            text-indent: 1.45cm;
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
            bottom: -5px;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, sans-serif;
        }

        .footer-left {
            position: absolute;
            left: .1cm;
            bottom: -5px;
        }

        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -5px;
        }

        .footer-right {
            position: absolute;
            right: .1cm;
            bottom: -5px;
        }
        
        .dynamic-text {
            display: inline;
            word-break: break-word;
        }

        .university-name {
            max-width: 45%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
    <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
    <span class="calibri-font"style="font-size:10pt;">Republic of the Philippines</span><br>
    <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
    <span class="calibri-font"style="font-size:10pt;">Province of Laguna</span><br>
    <br>
    <p class="office-title">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
    <p class="form-title">ORGANIZATION RENEWAL FORM</p>
    <br>
    </div>

    <div class="section right-align">
    <p><u><strong>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</strong></u></p>
        <p style="margin-top: 0; text-align: left; width: max-content; padding-left: 540px;">Date</p>
    </div>
    
    <div style="height: -3px;"></div>

    <div class="main-content">
        <div class="section left-align">
            <p style="margin-bottom:0px;"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
            <p style="margin-bottom:0px;"><strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong></p>
            <p style="margin-bottom:-10px;"><strong>LSPU</strong></p>
        </div>

        <div class="section">
            <p class="thru-line"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
        </div>

        <div class="section">
            <p style="margin-top:5px;">Sir/Madam:</p>
            <div style="height:15px;"></div>
            <p class="indented">The <span class="dynamic-text"><u><strong>{{ $application->organization_name }}</strong></u></span><span style="word-spacing:3px;"> wishes to seek renewal of its recognition to function as a duly recognized LSPU Organization for Academic Year 20</span><span class="dynamic-text"><u><strong>{{ $application->academic_year_start }}</strong></u></span> - 20<span class="dynamic-text"><u><strong>{{ $application->academic_year_end }}</strong></u></span>.</p>
            
            <p class="indented" style="word-spacing:9.5px;">In this connection, we are respectfully requesting from your good office to grant us permission to operate in our institution, subject to the existing rules<span style="margin-right:6px;"></span>    & regulations of our University.</p>
            <br>
            <p class="indented" style="margin-top:-10px; margin-left: 30px">Thank you very much.</p>
        </div>

        <div class="section right-align">
            <p class="respectfully-text" style="margin-left:calc(59% - 45px);">Very respectfully yours,</p>

            <div class="signature">
                <p><span class="signature-line" style="min-width:160px;"><strong>{{ $application->president_name }}</strong></span></p>
                <p><span class="title-under-signature title-left-adjust"><strong>Organization President</strong></span></p>
            </div>
        </div>

        <div class="section right-align">
            <div class="signature">
                @php
                    $orgNameLength = strlen($application->organization_name);
                    $orgName = $application->organization_name;
                    
                    if ($orgNameLength > 84) {
                        // Triple stack for names over 84 characters
                        $words = explode(' ', $orgName);
                        $totalWords = count($words);
                        $wordsPerLine = ceil($totalWords / 3);
                        $line1 = implode(' ', array_slice($words, 0, $wordsPerLine));
                        $line2 = implode(' ', array_slice($words, $wordsPerLine, $wordsPerLine));
                        $line3 = implode(' ', array_slice($words, $wordsPerLine * 2));
                        $stackedName = $line1 . '<br>' . $line2 . '<br>' . $line3;
                        $fontSize = 'font-size: 9pt;';
                        $textAlign = 'text-align: center;';
                        $lineHeight = 'line-height: 0.9;';
                    } elseif ($orgNameLength > 74) {
                        // Double stack for names over 74 characters
                        $words = explode(' ', $orgName);
                        $totalWords = count($words);
                        $wordsPerLine = ceil($totalWords / 2);
                        $line1 = implode(' ', array_slice($words, 0, $wordsPerLine));
                        $line2 = implode(' ', array_slice($words, $wordsPerLine));
                        $stackedName = $line1 . '<br>' . $line2;
                        $fontSize = 'font-size: 9pt;';
                        $textAlign = 'text-align: center;';
                        $lineHeight = 'line-height: 0.9;';
                    } elseif ($orgNameLength > 65) {
                        $stackedName = $orgName;
                        $fontSize = 'font-size: 10pt;';
                        $textAlign = '';
                        $lineHeight = '';
                    } else {
                        $stackedName = $orgName;
                        $fontSize = '';
                        $textAlign = '';
                        $lineHeight = '';
                    }
                @endphp
                <p style="margin-bottom: 2px;"><span class="signature-line" style="min-width:160px; {{ $fontSize }} {{ $textAlign }} {{ $lineHeight }}"><strong>{!! $stackedName !!}</strong></span></p>
                <p><span class="title-under-signature title-left-adjust-more"><strong>Name of Organization</strong></span></p>
            </div>
        </div>

        <div class="section left-align">
            <p><strong>NOTED:</strong></p>
            <div class="signature">
                <p><span class="signature-line" style="min-width:220px;"><strong>{{ $application->adviser_name }}</strong></span></p>
                <p><span class="title-under-signature title-right-adjust"><strong>Adviser/s, Student Organization</strong></span></p>
            </div>
            
            <div class="signature">
                <p><span class="signature-line" style="min-width:305px;"><strong>{{ $application->dean_name }}</strong></span></p>
                <p><span class="title-under-signature"><strong>Dean/Assoc. Dean, College of</strong> <span class="signature-line signature-line-inline" style="min-width:120px;"><strong>{{ $application->college ?? '' }}</strong></span></span></p>
            </div>
        </div>

        <div class="section center-align">
            <p style="margin-left:-380px;"><strong>Recommending Approval:</strong></p>
            <div class="signature">
                <p><span class="signature-line" style="min-width:270px;"><strong>{{ $application->coordinator_name }}</strong></span></p>
                <p><strong>Coordinator, Student Organization Unit</strong></p>
            </div>
        </div>

        <div class="section center-align">
            <p style="margin-left:-380px;"><strong>Approved / Disapproved:</strong></p>
            <div class="signature">
                <p><span class="signature-line" style="min-width:390px;"><strong>{{ $application->director_name }}</strong></span></p>
                <p><strong>Director/Chairperson, Office of Student Affairs and Services</strong></p>
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