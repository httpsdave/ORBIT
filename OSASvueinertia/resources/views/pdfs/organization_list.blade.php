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
            font-size: 12pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            font-size: 18px;
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
            border-spacing: 0 12px;
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
            padding: 1px 0;
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
            font-weight: normal;
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
            margin-top: 0.6cm;
        }

        .university-name {
            max-width: 60%;
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
                <span class="office-title">OFFICE OF STUDENT AFFAIRS AND SERVICES</span>
                <span class="sub-header">List of Members</span>
            </div>';
        return $header;
    }
    
    function showFooter() {
        // This function will generate the footer HTML
        $footer = '<div class="footer">
                <div class="footer-left">LSPU-OSAS-SF-005</div>
                <div class="footer-center">Rev. 1</div>
                <div class="footer-right">09 November 2020</div>
            </div>';
        return $footer;
    }
    
    function showSemesterInfo($application) {
        $info = '<div class="semester-section">
                <span>' . ($application->semester ?? '__') . ' Sem. / A.Y. ' . 
                20 . ($application->academic_year_start ?? '20__') . '-' . 
                20 . ($application->academic_year_end ?? '20__') . '</span>
            </div>
            <div class="section center-align">
                <p style="margin: 3px 0;">Name of Organization: <span class="signature-line">' . 
                ($application->organization_name ?? '') . '</span></p>
            </div>';
        return $info;
    }
    @endphp
    
    <!-- First page header -->
    {!! showHeader() !!}
    {!! showSemesterInfo($application) !!}
    
    <!-- Calculate total pages needed -->
    @php
        // Define how many members per page
        $membersPerPage = 10; // 5 rows with 2 columns = 10 members per page
        $totalMembers = $members->count();
        $totalPages = ceil($totalMembers / $membersPerPage);
        
        // For tracking which page we're on
        $currentPage = 1;
    @endphp

    <!-- First page members -->
    <div class="content">
        <table>
            @php
                $startIndex = 0;
                $endIndex = min($membersPerPage, $totalMembers);
                $membersPerColumn = 5;
            @endphp
            
            @for ($row = 0; $row < $membersPerColumn; $row++)
                <tr>
                    <!-- Left Column -->
                    <td>
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
                                        <div class="member-info">{{ $leftMember->student_name ?? '' }}</div>
                                        <div class="member-info">{{ $leftMember->student_number ?? '' }}</div>
                                        <div class="member-info">{{ $leftMember->course_year_section ?? '' }}</div>
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
                                        <div class="member-info"></div>
                                        <div class="member-info">Student Number</div>
                                        <div class="member-info">Course - Year Section</div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                    
                    <!-- Right Column -->
                    <td>
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
                                        <div class="member-info">{{ $rightMember->student_name ?? '' }}</div>
                                        <div class="member-info">{{ $rightMember->student_number ?? '' }}</div>
                                        <div class="member-info">{{ $rightMember->course_year_section ?? '' }}</div>
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
                                        <div class="member-info"></div>
                                        <div class="member-info">Student Number</div>
                                        <div class="member-info">Course - Year Section</div>
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

    <!-- Generate additional pages if needed -->
    @for ($page = 1; $page < $totalPages; $page++)
        <!-- Create a page break div -->
        <div class="page-break"></div>
        
        <!-- Repeat header on each page -->
        {!! showHeader() !!}
        {!! showSemesterInfo($application) !!}
        
        <!-- Calculate starting and ending indices for this page -->
        @php
            $startIdx = $page * $membersPerPage;
            $endIdx = min(($page + 1) * $membersPerPage, $totalMembers);
            $currentPage = $page + 1;
        @endphp
        
        <div class="content">
            <table>
                @for ($row = 0; $row < $membersPerColumn; $row++)
                    <tr>
                        <!-- Left Column -->
                        <td>
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
                                            <div class="member-info">{{ $leftMember->student_name ?? '' }}</div>
                                            <div class="member-info">{{ $leftMember->student_number ?? '' }}</div>
                                            <div class="member-info">{{ $leftMember->course_year_section ?? '' }}</div>
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
                                            <div class="member-info"></div>
                                            <div class="member-info">Student Number</div>
                                            <div class="member-info">Course - Year Section</div>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                        
                        <!-- Right Column -->
                        <td>
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
                                            <div class="member-info">{{ $rightMember->student_name ?? '' }}</div>
                                            <div class="member-info">{{ $rightMember->student_number ?? '' }}</div>
                                            <div class="member-info">{{ $rightMember->course_year_section ?? '' }}</div>
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
                                            <div class="member-info"></div>
                                            <div class="member-info">Student Number</div>
                                            <div class="member-info">Course - Year Section</div>
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
    @endfor

    <!-- Only add signature section on the last page -->
    @if($totalMembers > 0)
        @if($currentPage == $totalPages)
            <!-- Signatures - only on the last page -->
            <table class="signature-table" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                        <div style="width: 200px; margin: 0 auto;">
                            <p style="margin-bottom: 0;">
                                <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->adviser_name ?? '' }}</span>
                            </p>
                            <p style="margin-top: 2px; text-align: center; font-weight: bold;">Faculty Adviser</p>
                        </div>
                        <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: center; padding-top: 0;">
                        <div style="width: 200px; margin: 0 auto;">
                            <p style="margin-bottom: 0;">
                                <span class="date-signature-line" style="display: block; min-width: 200px; text-align: center;">{{ $application->second_adviser ?? '' }}</span>
                            </p>
                            <p style="margin-top: 2px; text-align: center; font-weight: bold;">Faculty Adviser</p>
                        </div>
                        <p style="text-align: left; padding-left: 10px; margin-top: 0;">Date: <span class="date-signature-line">{{ now()->format('F d, Y') }}</span></p>
                    </td>
                </tr>
            </table>
            <div class="noted-section center-align" style="margin-top: 5px;">
                <p style="margin: 2px 0;">Noted:</p>
            </div>

            <div class="dean-signature center-align" style="width: 100%; margin-top: 3px;">
                <p style="margin-bottom: 0;"><span class="date-signature-line">{{ $application->dean_name ?? '' }}</span></p>
                <p style="margin-top: 2px; font-weight: bold;">Dean/Assoc. Dean of College</p>
            </div>
        @endif
    @endif
</body>
</html>