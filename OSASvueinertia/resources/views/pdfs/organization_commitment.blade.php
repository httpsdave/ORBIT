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
            font-size: 11pt;
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
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 0.5cm 0; /* Reduced bottom margin */
            padding-top: 0.5cm; /* Added padding to keep it from the edge */
        }
        
        /* Changed font to Calibri with normal weight for these elements */
        .header-republic-text, .header-province-text {
            font-family: 'Calibri', sans-serif;
            font-weight: normal;
            font-size: 11pt;
        }

        .header-osas {
            font-size: 22px; /* Increased font size to 18px */
            padding-top: 20px; /* Moved down by 20px */
            margin-bottom: 5px; /* Reduced margin for closer spacing */
        }

  1 0    .commitment-title {
            margin-top: 5px; /* Single line spacing */
        }

        .section { 
            margin-bottom: 3px; /* Reduced from 5px to 3px */
        }

        .content {
            flex: 1; /* Pushes the footer down */
            margin-bottom: 130px; /* Reduced from 150px to 130px */
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
            margin: 2px 0; /* Reduced from 3px to 2px */
            word-wrap: break-word;
            line-height: 1.1; /* Slightly reduced from 1.15 */
        }
        
        .indented {
            text-indent: 1.45cm; /* Adjust the indent size as needed */
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
            min-width: 270px; /* Increased baseline width to match approval section */
            border-bottom: 1px solid black;
            padding-bottom: -1px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logo {
            position: absolute;
            top: -0.5cm; /* Adjust as needed */
            left: -2cm; /* Aligns with margin */
            width: 250px; /* Adjust size */
            height: auto;
        }

        .bottom-sections {
            position: absolute;
            bottom: 80px; /* Increased from 40px to 70px to move it higher by 30px */
            left: 0;
            right: 0;
            text-align: center;
        }

        .approval-section {
            margin-bottom: 20px; /* Increased from 10px to 20px for more spacing */
        }
        .approval-section p:first-child {
            margin-bottom: 20px; /* Add 20px spacing under the heading text */
        }

        .footer {
            position: absolute;
            bottom: -5px;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, Arial, sans-serif;
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
            bottom: 220px; /* Adjusted from 240px to 220px to match reduced spacing */
            left: 0;
            text-align: left;
            width: 50%;
        }
        
        /* Modified signature section - moved 30px more to the right */
        .signature-section {
            margin-top: 20px;
            float: right;
            width: 45%;
            margin-right: 30px; /* Reduced from 90px to 60px to move 30px to the right */
        }

        /* Fixed signature section styling for tight alignment */
        .signature-section p {
            margin: 0;
            padding: 2px 0; /* Reduced from 4px to 2px */
            clear: both;
        }

        /* Updated signature field styles - individual control for each underline */
        .signature-field {
            margin: 2px 0;
            display: table;
            width: 100%;
        }

        .signature-label {
            display: table-cell;
            vertical-align: bottom;
            padding-right: 1px;
            white-space: nowrap;
            width: 1%;
        }

        .signature-value {
            display: table-cell;
            border-bottom: 1px solid black;
            padding-bottom: -1px;
            text-align: left;
            min-height: 14px;
            vertical-align: bottom;
        }

        /* Individual width controls for each signature field */
        .sig-name { 
            width: 230px; 
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sig-signature { width: 205px; }
        .sig-college { width: 220px; }
        .sig-rank { width: 165px; }
        .sig-address { 
            width: 170px; 
            overflow: hidden;
            white-space: nowrap;
            text-overflow: clip;
        }
        .sig-contact { width: 143px; }
        .sig-date { width: 234px; }

        /* Restore paragraph styling for the title */
        .signature-section p:first-child {
            display: block;
            margin-bottom: 6px; /* Reduced from 10px to 6px */
        }

        /* Add extra space before the date field */
        .signature-section .signature-field:last-child {
            padding-top: 6px; /* Reduced from 10px to 6px */
        }
        
        /* Added styles for better spacing between specific sections */
        .address-block {
            margin-bottom: 15px; /* Increased space after address block */
        }
        
        .thru-line {
            margin-bottom: 15px; /* Reduced from 20px to 15px */
            padding-left: 1.27cm; /* Added indent to match other indented paragraphs */
            text-indent: 0; /* Ensures text starts at the padding position */
        }
        
        .sir-greeting {
            margin-top: 10px; /* Added space before "Sir," */
        }

        .university-name {
            max-width: 45%; /* Adjust as needed */
            height: auto;
            margin: 4px 0; /* Add some spacing above and below */
            display: inline-block;
        }

        .dynamic-text {
            display: inline;
            word-break: break-word;
        }

        /* Page break for multiple commitment forms */
        .page-break {
            page-break-before: always;
        }

        .commitment-page {
            min-height: 100vh;
            position: relative;
        }
    </style>
</head>
<body>
    @php
        // For commitment form, we'll support up to 2 advisers
        $advisers = $application->advisers ?? [];
        
        // If no advisers array exists, create one from the old single adviser fields
        if (empty($advisers)) {
            $advisers = [[
                'adviser_name' => $application->adviser_name ?? '',
                'adviser_prefix' => $application->adviser_prefix ?? '',
                'adviser_suffix' => $application->adviser_suffix ?? '',
                'adviser_college' => $application->adviser_college ?? '',
                'adviser_rank' => $application->adviser_rank ?? '',
                'adviser_address' => $application->adviser_address ?? '',
                'adviser_contact' => $application->adviser_contact ?? '',
            ]];
        }
        
        // Limit to maximum 2 advisers
        $advisers = array_slice($advisers, 0, 2);
    @endphp

    @foreach($advisers as $index => $adviser)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="commitment-page">
            @php
                // Define variables for address line breaks for current adviser
                $address = $adviser['adviser_address'] ?? '';
                $firstLine = '';
                $secondLine = '';
                $thirdLine = '';
                if (mb_strlen($address) > 25) {
                    $breakPos1 = mb_strrpos(mb_substr($address, 0, 25), ' ');
                    if ($breakPos1 === false) {
                        $breakPos1 = 25;
                    }
                    $firstLine = trim(mb_substr($address, 0, $breakPos1));
                    $remaining = trim(mb_substr($address, $breakPos1));
                    if (mb_strlen($remaining) > 42) {
                        $breakPos2 = mb_strrpos(mb_substr($remaining, 0, 42), ' ');
                        if ($breakPos2 === false) {
                            $breakPos2 = 42;
                        }
                        $secondLine = trim(mb_substr($remaining, 0, $breakPos2));
                        $thirdLine = trim(mb_substr($remaining, $breakPos2));
                    } else {
                        $secondLine = $remaining;
                    }
                } else {
                    $firstLine = $address;
                }

                // Define variables for college line breaks for current adviser
                $college = $adviser['adviser_college'] ?? '';
                $firstLineCollege = '';
                $secondLineCollege = '';
                $thirdLineCollege = '';
                if (mb_strlen($college) > 35) {
                    $breakPos1 = mb_strrpos(mb_substr($college, 0, 35), ' ');
                    if ($breakPos1 === false) {
                        $breakPos1 = 35;
                    }
                    $firstLineCollege = trim(mb_substr($college, 0, $breakPos1));
                    $remaining = trim(mb_substr($college, $breakPos1));
                    if (mb_strlen($remaining) > 42) {
                        $breakPos2 = mb_strrpos(mb_substr($remaining, 0, 42), ' ');
                        if ($breakPos2 === false) {
                            $breakPos2 = 42;
                        }
                        $secondLineCollege = trim(mb_substr($remaining, 0, $breakPos2));
                        $thirdLineCollege = trim(mb_substr($remaining, $breakPos2));
                    } else {
                        $secondLineCollege = $remaining;
                    }
                } else {
                    $firstLineCollege = $college;
                }

                // Define variables for adviser name with prefix/suffix for current adviser
                $adviserFullName = trim((isset($adviser['adviser_prefix']) && $adviser['adviser_prefix'] ? $adviser['adviser_prefix'] . ' ' : '') . ($adviser['adviser_name'] ?? '') . (isset($adviser['adviser_suffix']) && $adviser['adviser_suffix'] ? ', ' . $adviser['adviser_suffix'] : ''));
                $firstLineAdviserName = '';
                $secondLineAdviserName = '';
                
                // More conservative approach - split if longer than 28 characters to prevent CSS wrapping
                if (mb_strlen($adviserFullName) > 28) {
                    // Look for the last space before position 28
                    $breakPos = mb_strrpos(mb_substr($adviserFullName, 0, 28), ' ');
                    
                    // If no good space found, try to break after common prefixes
                    if ($breakPos === false || $breakPos < 8) {
                        // Check for common prefixes and break after them
                        $prefixes = ['ENGR.', 'DR.', 'PROF.', 'MR.', 'MS.', 'MRS.'];
                        foreach ($prefixes as $prefix) {
                            $prefixPos = mb_strpos($adviserFullName, $prefix);
                            if ($prefixPos === 0) {
                                $spaceAfterPrefix = mb_strpos($adviserFullName, ' ', mb_strlen($prefix));
                                if ($spaceAfterPrefix !== false && $spaceAfterPrefix < 28) {
                                    $breakPos = $spaceAfterPrefix;
                                    break;
                                }
                            }
                        }
                        
                        // Fallback: look for comma position
                        if ($breakPos === false || $breakPos < 8) {
                            $commaPos = mb_strpos($adviserFullName, ',');
                            if ($commaPos !== false && $commaPos <= 28 && $commaPos >= 15) {
                                $breakPos = $commaPos;
                            } else {
                                $breakPos = 28;
                            }
                        }
                    }
                    
                    $firstLineAdviserName = trim(mb_substr($adviserFullName, 0, $breakPos));
                    $secondLineAdviserName = trim(mb_substr($adviserFullName, $breakPos));
                } else {
                    $firstLineAdviserName = $adviserFullName;
                }
            @endphp

    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span class="header-republic-text"style="font-size:11pt;">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span class="header-province-text"style="font-size:11pt;">Province of Laguna</span><br>
        <br>
    <p class="office-title" style="margin-bottom:10px; font-size:11pt;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <span class="commitment-title" style="font-size:11pt;"><strong>ORGANIZATION ADVISER COMMITMENT FORM</strong></span>
    </div>

    <div class="section right-align">
        <p><u><strong>{{ \Carbon\Carbon::parse($application->form_date)->format('F d, Y') }}</strong></u></p>
        <p style="margin-top: 0; text-align: left; width: max-content; padding-left: 540px;">Date</p>
    </div>
    
    <div style="height: -3px;"></div>

    <div class="content">
        <div class="section">
            <p class="address-block"><strong>THE DIRECTOR/CHAIRPERSON<br>
            OFFICE OF STUDENT AFFAIRS AND SERVICES<br>
            LSPU</strong></p>
            
            <p class="thru-line"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
        </div>

        <div class="section justified">
            <p class="sir-greeting" style="margin-bottom:20px;">Sir/Madam:</p>
            <p class="indented"><span style="word-spacing:5px;">This letter is in connection with the application for recognition/renewal of</span> 
            <span class="dynamic-text"><u><strong>{!! $application->organization_name ?? '________________' !!}</strong></u></span> as a duly recognized LSPU Organization.</p>
            <p class="indented">I, the undersigned, have committed to serve as the organization's 
            Adviser for the academic year 20<u><strong>{!! $application->academic_year_start ?? '__' !!}</strong></u>-20<u><strong>{!! $application->academic_year_end ?? '__' !!}</strong></u>, and shall therefore assume full responsibility as 
            provided in the guidelines for the recognition of student organizations.</p>
            <p class="indented">Furthermore, I certify to the correctness and completeness of the documents 
            attached to the organization application for recognition.</p>
        </div>

        <!-- Updated signature section with individual underline length controls -->
        <div class="signature-section">
            <p style="margin-bottom: 18px;"><strong>Very respectfully yours,</strong></p>
            <div class="signature-field">
                <span class="signature-label">Name:</span>
                <span class="signature-value sig-name">
                    <strong>{!! $firstLineAdviserName !!}</strong>
                </span>
            </div>
            @if($secondLineAdviserName)
            <div class="signature-field" style="margin-left: 0;">
                <span class="signature-value sig-name" style="margin-left: 0; padding-left: 0;">
                    <strong>{!! $secondLineAdviserName !!}</strong>
                </span>
            </div>
            @endif
            <div class="signature-field">
                <span class="signature-label">Signature:</span>
                <span class="signature-value sig-signature">{!! $adviser['adviser_signature'] ?? '' !!}</span>
            </div>
            <div class="signature-field">
                <span class="signature-label">College:</span>
                <span class="signature-value sig-college">
                    <strong>{!! $firstLineCollege !!}</strong>
                </span>
            </div>
            @if($secondLineCollege)
            <div class="signature-field" style="margin-left: 0;">
                <span class="signature-value sig-college" style="margin-left: 0; padding-left: 0;">
                    <strong>{!! $secondLineCollege !!}</strong>
                </span>
            </div>
            @endif
            @if($thirdLineCollege)
            <div class="signature-field" style="margin-left: 0;">
                <span class="signature-value sig-college" style="margin-left: 0; padding-left: 0;">
                    <strong>{!! $thirdLineCollege !!}</strong>
                </span>
            </div>
            @endif
            <div class="signature-field">
                <span class="signature-label">Academic Rank:</span>
                <span class="signature-value sig-rank"><strong>{!! $adviser['adviser_rank'] ?? '' !!}</strong></span>
            </div>
            <div class="signature-field">
                <span class="signature-label">Home Address:</span>
                <span class="signature-value sig-address">
                    <strong>{!! $firstLine !!}</strong>
                </span>
            </div>
            @if($secondLine)
            <div class="signature-field" style="margin-left: 0;">
                <span class="signature-value sig-address" style="margin-left: 0; padding-left: 0;">
                    <strong>{!! $secondLine !!}</strong>
                </span>
            </div>
            @endif
            @if($thirdLine)
            <div class="signature-field" style="margin-left: 0;">
                <span class="signature-value sig-address" style="margin-left: 0; padding-left: 0;">
                    <strong>{!! $thirdLine !!}</strong>
                </span>
            </div>
            @endif
            <div class="signature-field">
                <span class="signature-label">Contact Number(s):</span>
                <span class="signature-value sig-contact"><strong>{!! $adviser['adviser_contact'] ?? '' !!}</strong></span>
            </div>
            <div class="signature-field">
                <span class="signature-label">Date:</span>
                <span class="signature-value sig-date"><strong>{{ \Carbon\Carbon::parse($application->form_date)->format('F d, Y') ?? '' }}</strong></span>
            </div>
        </div>

        <!-- Noted section aligned to the left margin -->
    <div class="noted-section" style="bottom: {{ ($secondLine || $thirdLine) ? '275px' : '315px' }}; left: 0;">
            <p style="margin-bottom: 20px;"><strong>Noted:</strong></p>
            <div>
                <p style="margin-left:65px;"><span class="underline" style="min-width:180px;"><strong>{!! $application->dean_name ?? '' !!}</strong></span></p>
                <p style="margin-left:65px;"><strong>Dean/Assoc. Dean of College</strong></p>
            </div>
        </div>

        <!-- Bottom sections positioned at bottom center -->
    <div class="bottom-sections" style="bottom: {{ ($secondLine || $thirdLine) ? '40px' : '80px' }};">
            <div class="approval-section" style="margin-bottom: 25px;"> <!-- Spacing between sections -->
                <p style="margin-bottom: 20px;"><strong>Recommending Approval:</strong></p> <!-- Added 20px spacing under this text -->
                <p><strong><span class="underline" style="min-width:270px;">{!! $application->coordinator_name ?? '_______________________________' !!}</span></strong></p>
                <p><strong>Coordinator, Student Organization Unit</strong></p>
            </div>

            <div class="approval-section">
                <p style="margin-bottom: 20px;"><strong>Approved / Disapproved:</strong></p> <!-- Added 20px spacing under this text -->
                <p><strong><span class="underline" style="min-width:380px;">{!! $application->director_name ?? '_______________________________' !!}</span></strong></p>
                <p><strong>Director/Chairperson, Office of Student Affairs and Services</strong></p>
            </div>
        </div> <!-- End signature-section -->
        
    </div> <!-- End content -->

    <div class="footer">
        <div class="footer-left">LSPU-OSAS-SF-003</div>
        <div class="footer-center">Rev.<span style="display: inline-block !important; border-bottom: 1px solid black !important; line-height: 5px !important; position: relative !important; top: 2px !important;">1</span></div>
        <div class="footer-right">09 November 2020</div>
    </div>
        
    </div> <!-- End commitment-page -->
    @endforeach

</body>
</html>