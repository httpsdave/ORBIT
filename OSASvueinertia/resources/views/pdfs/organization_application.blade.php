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
        
        /* Preserve the original spacing */
        .original-spacing {
            margin-top: 0;
            margin-bottom: 0;
        }
        .university-name {
            max-width: 45%; /* Smaller width for the image */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }
        .calibri-text {
            font-family: Calibri, sans-serif;
        }

        .dynamic-text {
            display: inline;
            word-break: break-word;
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
            padding-right: 110px; /* Adjusted to move it 15px more to the left */
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
    <span class="calibri-text" style="font-size:11pt;">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
    <span class="calibri-text" style="font-size:11pt;">Province of Laguna</span><br>
        <br>
       <strong> OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
        <br>
        <span class="sub-header"><strong>APPLICATION FOR ORGANIZATION RECOGNITION/RENEWAL OF ACCREDITED STUDENT ORGANIZATION</strong></span>
    </div>

    <div class="section right-align">
    <p><u><strong>{{ \Carbon\Carbon::parse($application->application_date)->format('F d, Y') }}</strong></u></p>
        <p style="margin-top: 0; text-align: left; width: max-content; padding-left: 540px;">Date</p>
    </div>
    
    <div>
        <p><strong>THE DIRECTOR/CHAIRPERSON</strong><br>Office of Student Affairs and Services<br>LSPU</p>
    </div>

    <div style="height: 7px;"></div>
    <div class="section justified">
        <p>Sir/Madam:</p>
    <div style="height: 12px;"></div>
    <p class="indented"><span style="word-spacing:10px;">I have the honor to apply for recognition/renewal of the organization,</span> <span class="dynamic-text"><u><strong>{{ $application->organization_name?: 'Organization Name' }}</strong></u></span>, to be duly recognized by Laguna State Polytechnic University.</p>
    <div style="height: 5px;"></div>
        <p class="indented">In compliance with CHED Memo Order No. 9s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII-Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation, to wit:</p>
    </div>

    <div class="section list-indented">
    <p style="position: relative; left: -5px;">1. Letter of application for Organization Recognition (for new organizations) / Organization 
     <p style="position: relative; left: 15px;"> Renewal Form (for organizations seeking renewal) <span style="position: absolute; right: 70px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">2. Constitution and By-Laws of the Organization <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">3. Plan of activities for one (1) year <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">4. Accomplishment reports (for renewal of accreditation) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">5. Adviser(s) Commitment Form <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">6. Certification from respective Dean/Associate Dean <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    <p style="position: relative; left: -5px;">7. Financial Report (if any) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    </div>

    <div class="section justified">
        <p class="indented">It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition is good only for one (1) school year, subject to renewal unless revoked prior to its expiration.</p>
    </div>

    <div class="section respectfully-yours">
        <p>Respectfully yours,</p>
    </div>

    <div class="signature right-align">
    <p><span class="signature-line"><strong>{!! $application->president_name ?: '&nbsp;' !!}</strong></span></p>
        <p><span class="title-text">Organization President</span></p>
    </div>

    <div class="signature right-align">
        @php
            $orgNameLength = strlen($application->organization_name);
            $orgName = $application->organization_name;
            
            if ($orgNameLength > 74) {
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
        <p style="margin-bottom: 2px;"><span class="signature-line" style="{{ $fontSize }} {{ $textAlign }} {{ $lineHeight }}"><strong>{!! $stackedName !!}</strong></span></p>
        <p><span class="title-text">Name of Organization</span></p>
    </div>

    <div class="section left-align">
    <p style="margin-top: -10px; margin-bottom: 0;">NOTED:</p>
    </div>

    <table style="width: 100%; margin-top: 2px;">
        <tr style="vertical-align: top;">
            <td style="width: 50%; text-align: left;">
                <div class="signature left-align">
                    @php
                        $adviserFullName = trim((isset($application->adviser_prefix) && $application->adviser_prefix ? $application->adviser_prefix . ' ' : '') . ($application->adviser_name ?? 'N/A') . (isset($application->adviser_suffix) && $application->adviser_suffix ? ', ' . $application->adviser_suffix : ''));
                        $adviserNameLength = strlen($adviserFullName);
                        
                        if ($adviserNameLength > 39) {
                            // Over 39 characters: allow double stacking with 10pt font
                            $words = explode(' ', $adviserFullName);
                            $totalWords = count($words);
                            $wordsPerLine = ceil($totalWords / 2);
                            $line1 = implode(' ', array_slice($words, 0, $wordsPerLine));
                            $line2 = implode(' ', array_slice($words, $wordsPerLine));
                            $adviserDisplayName = $line1 . '<br>' . $line2;
                            $adviserFontSize = 'font-size: 11pt; text-align: center; line-height: 0.9;';
                        } elseif ($adviserNameLength > 32) {
                            // 33-39 characters: 11pt font with no wrapping
                            $adviserDisplayName = $adviserFullName;
                            $adviserFontSize = 'font-size: 11pt; white-space: nowrap;';
                        } else {
                            // 32 characters or less: normal styling
                            $adviserDisplayName = $adviserFullName;
                            $adviserFontSize = '';
                        }
                    @endphp
                    <p><span class="signature-line" style="{{ $adviserFontSize }}"><strong>{!! $adviserDisplayName ?: '&nbsp;' !!}</strong></span></p>
                    <p style="text-align:left;"><span class="title-text">Adviser, Student Organization</span></p>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="signature right-align">
                    @php
                        $deanFullName = trim((isset($application->dean_prefix) && $application->dean_prefix ? $application->dean_prefix . ' ' : '') . ($application->dean_name ?? '') . (isset($application->dean_suffix) && $application->dean_suffix ? ', ' . $application->dean_suffix : ''));
                        $deanNameLength = strlen($deanFullName);
                        
                        if ($deanNameLength > 39) {
                            // Over 39 characters: allow double stacking with 10pt font
                            $words = explode(' ', $deanFullName);
                            $totalWords = count($words);
                            $wordsPerLine = ceil($totalWords / 2);
                            $line1 = implode(' ', array_slice($words, 0, $wordsPerLine));
                            $line2 = implode(' ', array_slice($words, $wordsPerLine));
                            $deanDisplayName = $line1 . '<br>' . $line2;
                            $deanFontSize = 'font-size: 11pt; text-align: center; line-height: 0.9;';
                        } elseif ($deanNameLength > 32) {
                            // 33-39 characters: 11pt font with no wrapping
                            $deanDisplayName = $deanFullName;
                            $deanFontSize = 'font-size: 11pt; white-space: nowrap;';
                        } else {
                            // 32 characters or less: normal styling
                            $deanDisplayName = $deanFullName;
                            $deanFontSize = '';
                        }
                    @endphp
                    <p><span class="signature-line" style="{{ $deanFontSize }}"><strong>{!! $deanDisplayName ?: '&nbsp;' !!}</strong></span></p>
                    <p><span class="title-text">Dean/Assoc. Dean of College</span></p>
                </div>
            </td>
        </tr>
    </table>



    <div class="section center-align" style="margin-bottom: 0;">
    <p style="margin-bottom: 0;"><strong>Recommending Approval:</strong></p>
    </div>
    <div class="signature center-align" style="margin-top: 0;">
    <p style="margin-bottom: 0; margin-top: -10px;"><strong><span class="signature-line" style="min-width:270px;">{{ $application->coordinator_name ?: '&nbsp;' }}</span></strong></p>
    <p style="margin-top: 0; margin-bottom: 0;"><span class="title-text long-title">Coordinator, Student Organization Unit</span></p>
    <div style="height: 5px;"></div>
    </div>

    <div class="section center-align last-section" style="margin-bottom: 0;">
    <p style="margin-bottom: 0;"><strong>Approved/Disapproved:</strong></p>
    </div>
    <div class="signature center-align last-signature" style="margin-top: 0;">
    <p style="margin-bottom: 0; margin-top: -6px;"><strong><span class="signature-line" style="min-width:380px;">{{ $application->director_name ?: '&nbsp;' }}</span></strong></p>
    <p style="margin-top: 0; margin-bottom: 0;"><span class="title-text long-title">Director/Chairperson, Office of Student Affairs and Services</span></p>
    </div>

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-001</div>
        <div class="footer-center">Rev.<span style="display: inline-block !important; border-bottom: 1px solid black !important; line-height: 5px !important; position: relative !important; top: 2px !important;">1</span></div>
        <div class="footer-right">09 November 2020</div>
    </div>

</body>
</html>