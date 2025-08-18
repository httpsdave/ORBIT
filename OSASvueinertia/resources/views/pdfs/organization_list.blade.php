<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Members - Student Organization</title>
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
        }

        .header {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 0.3cm 0;
            padding-top: 0.3cm;
        }
        
        /* Make Republic of the Philippines and Province of Laguna not bold */
        .header-text {
            font-weight: normal;
            font-family: 'Calibri', sans-serif;
            font-size:11pt;
        }

        /* Add more space after Province of Laguna */
        .province-text {
            margin-bottom: 12px; /* Added space after Province of Laguna */
            display: block;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }

        /* Table spacing adjustments */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }

        td {
            vertical-align: top;
            padding: 0;
            width: 50%;
        }

        .member-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        .photo-cell {
            width: 96px;
            vertical-align: top;
            padding-right: 10px;
        }

        .info-cell {
            vertical-align: top;
            width: calc(100% - 106px);
            text-align: center;
        }

        .info-cell-spaced {
            padding-left: 60px;
            padding-right: 60px;
        }

        .photo-box {
            border: 1px solid black;
            width: 96px;
            height: 96px;
            text-align: center;
            line-height: 96px;
            font-size: 10pt;
            position: relative;
            overflow: hidden;
        }
        
        .photo-box-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
            font-size: 10pt;
        }

        .member-info {
            margin-bottom: 3px;
            min-height: 15px;
            padding: -3px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            display: block;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 11pt;
            font-family: 'Calibri', sans-serif;
            font-weight: normal !important;
        }

        /* Ensure all footer text is normal, not bold, on all pages */
        .footer, .footer * {
            font-weight: normal !important;
            font-style: normal !important;
            font-family: Calibri, sans-serif !important;
        }

        .footer-left {
            position: absolute;
            left: -1.0cm;
            bottom: 0;
            font-weight: normal !important;
        }

        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            font-weight: normal !important;
        }

        .footer-right {
            position: absolute;
            right: -1.0cm;
            bottom: 0;
            font-weight: normal !important;
        }
        
        /* Additional rules to ensure footer text is not bold */
        .footer * {
            font-weight: normal !important;
        }
        
        .footer div {
            font-weight: normal !important;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 15px;
        }

        .signature {
            margin-top: 15px;
            width: 45%;
        }

        .signature p {
            margin: 2px 0;
        }

        .signature-line {
            display: inline;
            border-bottom: 1px solid black;
            padding-bottom: 1px;
            text-align: center;
        }
        
        .date-signature-line {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid black;
            padding-bottom: 1px;
            text-align: center;
        }

        .center-align {
            text-align: center;
        }

        .right-align {
            text-align: right;
        }

        .left-align {
            text-align: left;
        }
        
        .semester-section {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .content {
            margin-top: 0.1cm;
        }

        .university-name {
            max-width: 55%;
            height: auto;
            margin: 3px 0;
            display: inline-block;
        }

        .signature-section {
            margin-top: 15px;
        }
        
        .signature-table td {
            padding-top: 5px;
        }
        
        .noted-section {
            margin-top: 10px;
        }
        
        .dean-signature {
            margin-top: 5px;
        }

        /* Office title with additional space */
        .office-title {
            margin-top: 10px; /* Added space before office title */
            display: block;
        }

        /* Additional space before "List of Members" */
        .sub-header {
            margin-top: 6px;
            display: block;
        }

        .underline {
            position: relative;
            border-bottom: 1px solid #000;
            width: 220px; /* Increased width for even longer underline */
            margin: 6px auto;
            height: 16px;
            text-align: center;
        }
        .filled-text {
            position: relative;
            z-index: 2;
            background: white;
            padding: 0 2px;
        }
        .spacer-cell {
            width: 60px;
            min-width: 60px;
            max-width: 120px;
        }
    </style>
</head>
<body>
    <!-- Function to generate header -->
    @php
    function showHeader() {
        // This function will be called to generate the header for each page
        $header = '<div class="header">
                <img src="' . public_path('images/lspu-logo.png') . '" alt="LSPU Logo" class="logo">
                <span class="header-text">Republic of the Philippines<br>
                <img src="' . public_path('images/lspu-name.png') .  '" alt="Laguna State Polytechnic University" class="university-name"><br>
                <span class="province-text">Province of Laguna</span></span>
                <p class="office-title" style="font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:5px;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
                <p class="sub-header" style="font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:5px;">LIST OF MEMBERS OF THE ORGANIZATION</p>
            </div>';
        return $header;
    }
    
    function showFooter() {
        // Footer styling copied from organization_plan.blade.php (no functional changes)
        $footer = '<div class="footer" style="position: absolute; bottom: -5px; width: 100%; height: 20px; line-height: 20px; font-size: 10pt; font-family: Calibri, sans-serif; font-weight: normal;">
            <div class="footer-left" style="position: absolute; left: .1cm; bottom: -5px; font-weight: normal;">LSPU-OSAS-SF-005</div>
            <div class="footer-center" style="position: absolute; left: 50%; transform: translateX(-50%); bottom: -5px; font-weight: normal;">Rev. 1</div>
            <div class="footer-right" style="position: absolute; right: .1cm; bottom: -5px; font-weight: normal;">09 November 2020</div>
        </div>';
        return $footer;
    }
    
    function showSemesterInfo($application) {
        $info = '<div style="width:100%; text-align:center; margin-top:-22px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold;">
            <span style="display:inline-block; text-align:center;">
                <span class="signature-line" style="min-width:40px; margin-bottom:-2px; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center;">
                    <span style="position:relative; top:0px; font-weight:bold;">' . ($application->semester ?? '<b>1st</b>') . '</span>
                </span>
                Semester AY 20
                <span class="signature-line" style="min-width:40px; margin-bottom:-2px; margin-top:-1px; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center;">
                    <span style="position:relative; top:1px; font-weight:bold;">' . ($application->academic_year_start ?? '<b>24</b>') . '</span>
                </span>
                -20
                <span class="signature-line" style="min-width:40px; margin-bottom:-2px; margin-top:-1px; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center;">
                    <span style="position:relative; top:1px; font-weight:bold;">' . ($application->academic_year_end ?? '<b>25</b>') . '</span>
                </span>
            </span>
        </div>';
        $info .= '<div style="width:100%; margin-top: 15px; text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                <span style="font-family: Times New Roman, serif; font-size: 11pt; font-weight: normal; text-align: left;">Name of Organization</span>
                <span class="signature-line" style="margin-bottom:0px; min-width:200px; font-family: Times New Roman, serif; font-size: 11pt; text-align: center; border-bottom: 1px solid #000; display: inline-block;">
                    <span style="font-weight: bold;">' . ($application->organization_name ?? '') . '</span>
                </span>
            </div>
        </div>';
        return $info;
    }
    @endphp
    
    <!-- First page header -->
    {!! showHeader() !!}
    {!! showSemesterInfo($application) !!}

    <!-- Centered single member field under Name of Organization with hardcoded sample data (never uses real data) -->

    <div style="width: 340px; margin: 0 auto -5px auto;">
    <div style="text-align: center; font-weight: normal; margin-bottom: 4px; margin-top: 10px;">SAMPLE FORMAT:</div>
    <table class="member-table" style="margin: 0 auto; margin-top: 10px;">
            <tr>
                <td class="photo-cell">
                    <div class="photo-box">
                        <span class="photo-box-text">1 x 1</span>
                    </div>
                </td>
                <td class="info-cell">
                    <div class="member-info underline">
                        <span class="filled-text">(Signature Over Printed Name)</span>
                    </div>
                    <div class="member-info underline">
                        <span class="filled-text">(Student Number)</span>
                    </div>
                    <div class="member-info underline">
                        <span class="filled-text">(Course / Year Section)</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @php
        // Define how many members per page
    $membersPerPage = 8; // 4 rows with 2 columns = 8 members per page
        $totalMembers = $members->count();
        $totalPages = ceil($totalMembers / $membersPerPage);
        
        // For tracking which page we're on
        $currentPage = 1;
    @endphp

    <!-- First page members -->
    <div class="content">
    <table style="margin-left: -35px;">
            @php
                $startIndex = 0;
                $endIndex = min($membersPerPage, $totalMembers);
            $membersPerColumn = 4;
            @endphp
            
            @for ($row = 0; $row < $membersPerColumn; $row++)
                <tr>
                    <!-- Left Column -->
                    <td style="padding-right: 15px;"> <!-- Increased right padding for left column -->
                        @php
                            $leftIndex = $row;
                            $leftMember = $members[$leftIndex] ?? null;
                        @endphp
                        
                        @if ($leftIndex < $totalMembers && $leftMember)
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box">
                                            @if($leftMember->photo_path)
                                                <img src="{{ storage_path('app/public/' . $leftMember->photo_path) }}" alt="Member Photo" width="94" height="94">
                                            @else
                                                <span class="photo-box-text">1 x 1</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->student_name && $leftMember->student_name !== 'Sample Data')
                                                <span class="filled-text">{{ $leftMember->student_name }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->student_number && $leftMember->student_number !== '0322-1234' && $leftMember->student_number !== '0322-5678')
                                                <span class="filled-text">{{ $leftMember->student_number }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->course_year_section && $leftMember->course_year_section !== 'Sample Data')
                                                <span class="filled-text">{{ $leftMember->course_year_section }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box"><span class="photo-box-text">1 x 1</span></div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->student_name)
                                                <span class="filled-text">{{ $leftMember->student_name }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->student_number)
                                                <span class="filled-text">{{ $leftMember->student_number }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($leftMember && $leftMember->course_year_section)
                                                <span class="filled-text">{{ $leftMember->course_year_section }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                    
                    <!-- Right Column -->
                    <td style="padding-left: 15px;"> <!-- Increased left padding for right column -->
                        @php
                            $rightIndex = $row + $membersPerColumn;
                            $rightMember = $members[$rightIndex] ?? null;
                        @endphp
                        
                        @if ($rightIndex < $totalMembers && $rightMember)
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box">
                                            @if($rightMember->photo_path)
                                                <img src="{{ storage_path('app/public/' . $rightMember->photo_path) }}" alt="Member Photo" width="94" height="94">
                                            @else
                                                <span class="photo-box-text">1 x 1</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->student_name && $rightMember->student_name !== 'Sample Data')
                                                <span class="filled-text">{{ $rightMember->student_name }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->student_number && $rightMember->student_number !== '0322-1234' && $rightMember->student_number !== '0322-5678')
                                                <span class="filled-text">{{ $rightMember->student_number }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->course_year_section && $rightMember->course_year_section !== 'Sample Data')
                                                <span class="filled-text">{{ $rightMember->course_year_section }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <table class="member-table">
                                <tr>
                                    <td class="photo-cell">
                                        <div class="photo-box"><span class="photo-box-text">1 x 1</span></div>
                                    </td>
                                    <td class="info-cell">
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->student_name)
                                                <span class="filled-text">{{ $rightMember->student_name }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->student_number)
                                                <span class="filled-text">{{ $rightMember->student_number }}</span>
                                            @endif
                                        </div>
                                        <div class="member-info underline">
                                            @if($rightMember && $rightMember->course_year_section)
                                                <span class="filled-text">{{ $rightMember->course_year_section }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>
            @endfor
        </table>
    </div>

    <!-- First page footer -->
    {!! showFooter() !!}

    <!-- Signatures - on first page -->
    @if($totalMembers > 0)
        <table class="signature-table" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                    <div style="width: 200px; margin: 0 auto;">
                        <p style="margin-bottom: 0;">
                            <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->adviser_name ?? '' }}</span>
                        </p>
                        <p style="margin-top: 2px; text-align: center;">Organization Adviser</p>
                    </div>
                    <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                    <div style="width: 200px; margin: 0 auto;">
                        <p style="margin-bottom: 0;">
                            <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->second_adviser ?? '' }}</span>
                        </p>
                        <p style="margin-top: 2px; text-align: center;">Organization Adviser</p>
                    </div>
                    <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                </td>
            </tr>
        </table>
        <div class="dean-signature center-align" style="width: 50% !important; margin-top: 3px; text-align: center !important;">
            <p style="margin-bottom: 0; font-weight: bold !important; margin-left: 10px !important; text-align: left !important;">Noted:</p>
            <p style="margin-bottom: 0; text-align: center !important;"><span class="date-signature-line" style="display: inline-block;">{{ $application->dean_name ?? '' }}</span></p>
            <p style="margin-top: 2px; font-weight: normal; margin-left: 0 !important; text-align: center !important;">Dean/Assoc. Dean of College</p>
        </div>
    @endif

    <!-- Generate additional pages if needed -->
    @for ($page = 1; $page < $totalPages; $page++)
        <!-- Create a page break div -->
        <div class="page-break"></div>
        
        <!-- Repeat header on each page -->
        {!! showHeader() !!}
        {!! showSemesterInfo($application) !!}

        <!-- Centered single member field under Name of Organization with hardcoded sample data (never uses real data) -->
        <div style="width: 340px; margin: 0 auto -5px auto;">
            <div style="text-align: center; font-weight: normal; margin-bottom: 4px;">SAMPLE FORMAT:</div>
            <table class="member-table" style="margin: 0 auto;">
                <tr>
                    <td class="photo-cell">
                        <div class="photo-box">
                            <span class="photo-box-text">1 x 1</span>
                        </div>
                    </td>
                    <td class="info-cell">
                        <div class="member-info underline">
                            <span class="filled-text">(Signature Over Printed Name)</span>
                        </div>
                        <div class="member-info underline">
                            <span class="filled-text">(Student Number)</span>
                        </div>
                        <div class="member-info underline">
                            <span class="filled-text">(Course / Year Section)</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Calculate starting and ending indices for this page -->
        @php
            $startIdx = $page * $membersPerPage;
            $endIdx = min(($page + 1) * $membersPerPage, $totalMembers);
            $currentPage = $page + 1;
        @endphp
        
        <div class="content">
            <table style="margin-left: -35px;">
                @for ($row = 0; $row < $membersPerColumn; $row++)
                    <tr>
                        <!-- Left Column -->
                        <td style="padding-right: 15px;"> <!-- Increased right padding for left column -->
                            @php
                                $leftIndex = $startIdx + $row;
                                $leftMember = isset($members[$leftIndex]) ? $members[$leftIndex] : null;
                            @endphp
                            
                            @if ($leftIndex < $totalMembers && $leftMember)
                                <table class="member-table">
                                    <tr>
                                        <td class="photo-cell">
                                            <div class="photo-box">
                                                @if($leftMember->photo_path)
                                                    <img src="{{ storage_path('app/public/' . $leftMember->photo_path) }}" alt="Member Photo" width="94" height="94">
                                                @else
                                                    <span class="photo-box-text">1 x 1</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="info-cell">
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->student_name)
                                                    <span class="filled-text">{{ $leftMember->student_name }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->student_number)
                                                    <span class="filled-text">{{ $leftMember->student_number }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->course_year_section)
                                                    <span class="filled-text">{{ $leftMember->course_year_section }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <table class="member-table">
                                    <tr>
                                        <td class="photo-cell">
                                            <div class="photo-box"><span class="photo-box-text">1 x 1</span></div>
                                        </td>
                                        <td class="info-cell">
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->student_name)
                                                    <span class="filled-text">{{ $leftMember->student_name }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->student_number)
                                                    <span class="filled-text">{{ $leftMember->student_number }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($leftMember && $leftMember->course_year_section)
                                                    <span class="filled-text">{{ $leftMember->course_year_section }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                        
                        <!-- Right Column -->
                        <td style="padding-left: 15px;"> <!-- Increased left padding for right column -->
                            @php
                                $rightIndex = $startIdx + $row + $membersPerColumn;
                                $rightMember = isset($members[$rightIndex]) ? $members[$rightIndex] : null;
                            @endphp
                            
                            @if ($rightIndex < $totalMembers && $rightMember)
                                <table class="member-table">
                                    <tr>
                                        <td class="photo-cell">
                                            <div class="photo-box">
                                                @if($rightMember->photo_path)
                                                    <img src="{{ storage_path('app/public/' . $rightMember->photo_path) }}" alt="Member Photo" width="94" height="94">
                                                @else
                                                    <span class="photo-box-text">1 x 1</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="info-cell">
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->student_name)
                                                    <span class="filled-text">{{ $rightMember->student_name }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->student_number)
                                                    <span class="filled-text">{{ $rightMember->student_number }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->course_year_section)
                                                    <span class="filled-text">{{ $rightMember->course_year_section }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <table class="member-table">
                                    <tr>
                                        <td class="photo-cell">
                                            <div class="photo-box"><span class="photo-box-text">1 x 1</span></div>
                                        </td>
                                        <td class="info-cell">
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->student_name)
                                                    <span class="filled-text">{{ $rightMember->student_name }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->student_number)
                                                    <span class="filled-text">{{ $rightMember->student_number }}</span>
                                                @endif
                                            </div>
                                            <div class="member-info underline">
                                                @if($rightMember && $rightMember->course_year_section)
                                                    <span class="filled-text">{{ $rightMember->course_year_section }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                @endfor
            </table>
        </div>
        
        <!-- Add footer to each page -->
        {!! showFooter() !!}
        
        <!-- Signatures - on every page -->
        @if($totalMembers > 0)
            <table class="signature-table" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                        <div style="width: 200px; margin: 0 auto;">
                            <p style="margin-bottom: 0;">
                                <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->adviser_name ?? '' }}</span>
                            </p>
                            <p style="margin-top: 2px; text-align: center; font-weight: normal;">Organization Adviser</p>
                        </div>
                        <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                        <div style="width: 200px; margin: 0 auto;">
                            <p style="margin-bottom: 0;">
                                <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->second_adviser ?? '' }}</span>
                            </p>
                            <p style="margin-top: 2px; text-align: center; font-weight: normal;">Organization Adviser</p>
                        </div>
                        <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                    </td>
                </tr>
            </table>
            <div class="dean-signature center-align" style="width: 50% !important; margin-top: 3px; text-align: center !important;">
                <p style="margin-bottom: 0; font-weight: bold !important; margin-left: -10px !important; text-align: left !important;">Noted:</p>
                <p style="margin-bottom: 0; text-align: center !important;"><span class="date-signature-line" style="display: inline-block;">{{ $application->dean_name ?? '' }}</span></p>
                <p style="margin-top: 2px; font-weight: normal; margin-left: -20px !important; text-align: center !important;">Dean/Assoc. Dean of College</p>
            </div>
        @endif
    @endfor
</body>
</html>