<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recognized Student Organizations - {{ $academicYear }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 1.27cm;
            margin-right: 1.27cm;
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
            font-weight: normal;
            margin: 15px 0 0.3cm 0;
            padding-top: 0.3cm;
            line-height: 1.3;
        }

        .logo {
            position: absolute;
            top: -0.5cm;
            left: 0.5cm;
            width: 250px;
            height: auto;
        }

        .logo-right {
            position: absolute;
            top: 0;
            right: 2.5cm;
            width: 100px;
            height: auto;
        }

        .university-name {
            max-width: 35%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }

        .calibri-text {
            font-family: Calibri, sans-serif;
        }

        .office-heading {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 0.3cm;
        }

        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 0.5cm;
            margin-bottom: 0.3cm;
            text-align: center;
        }

        .subsection-title {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 0.4cm;
            margin-bottom: 0.2cm;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.3cm;
            font-size: 11pt;
            font-family: Calibri, sans-serif;
        }

        th, td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            font-family: Calibri, sans-serif;
        }

        th {
            background-color: #000000;
            text-align: center;
            color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 10px;
        }

        .signature-section {
            margin-top: 1.5cm;
            font-size: 11pt;
        }

        .signature-block {
            margin-top: 0.5cm;
            text-align: center;
        }

        .signature-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid black;
            margin-bottom: 3px;
        }

        .signature-label {
            font-size: 10pt;
            font-style: italic;
        }

        .page-break {
            page-break-after: always;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 20px;
            line-height: 20px;
            font-size: 10pt;
            font-family: Calibri, sans-serif;
        }

        .footer-left {
            position: absolute;
            left: 0;
            bottom: 0.3cm;
        }

        .footer-left img {
            height: 60px;
            width: auto;
        }

        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
        }

        .footer-right {
            position: absolute;
            right: 0;
            bottom: 0.3cm;
            font-style: italic;
            font-size: 10pt;
        }

        /* Prevent table rows from breaking across pages */
        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if(file_exists(public_path('images/lspu-logo.png')))
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        @endif
        @if(file_exists(public_path('images/bagongpilipinaslogo.png')))
        <img src="{{ public_path('images/bagongpilipinaslogo.png') }}" alt="Bagong Pilipinas Logo" class="logo-right">
        @endif
        <span class="calibri-text" style="font-size:11pt;">Republic of the Philippines</span><br>
        @if(file_exists(public_path('images/lspu-name.png')))
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        @else
        <strong style="font-size:12pt; font-family:'Old English Text MT', 'Book Antiqua', serif;">LAGUNA STATE POLYTECHNIC UNIVERSITY</strong><br>
        @endif
        <span class="calibri-text" style="font-size:11pt;">Province of Laguna</span><br>
        <br>
        <span class="office-heading">OFFICE OF THE STUDENT AFFAIRS AND SERVICES</span>
    </div>

    <!-- Title -->
    <div class="section-title">
        RECOGNIZED STUDENT ORGANIZATION<br>
        <div style="margin-top: 0.3cm;">ACADEMIC YEAR {{ $academicYear }}</div>
    </div>

    @if(!empty($studentCouncils) && count($studentCouncils) > 0)
    <!-- Student Council Section -->
    <div class="subsection-title" style="margin-left: 5%;">STUDENT COUNCIL:</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 37%;">Name of Organization</th>
                <th style="width: 25%;">Name of President</th>
                <th style="width: 33%;">Name of Organization Adviser</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentCouncils as $index => $org)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ strtoupper($org['name']) }}</td>
                <td>{{ strtoupper($org['president'] ?? '') }}</td>
                <td>{{ strtoupper($org['adviser'] ?? '') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($subOrganizations) && count($subOrganizations) > 0)
    <!-- Sub-Organization Section -->
    <div class="subsection-title" style="margin-left: 5%;">SUB-ORGANIZATION:</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 37%;">Name of Organization</th>
                <th style="width: 25%;">Name of President</th>
                <th style="width: 33%;">Name of Organization Adviser</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subOrganizations as $index => $org)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ strtoupper($org['name']) }}</td>
                <td>{{ strtoupper($org['president'] ?? '') }}</td>
                <td>{{ strtoupper($org['adviser'] ?? '') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($newRecognizedOrganizations) && count($newRecognizedOrganizations) > 0)
    <!-- New Recognized Organization Section -->
    <div class="subsection-title" style="margin-top: 0.4cm; margin-left: 5%;">NEW RECOGNIZED ORGANIZATION:</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 37%;">Name of Organization</th>
                <th style="width: 25%;">Name of President</th>
                <th style="width: 33%;">Name of Organization Adviser</th>
            </tr>
        </thead>
        <tbody>
            @foreach($newRecognizedOrganizations as $index => $org)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ strtoupper($org['name']) }}</td>
                <td>{{ strtoupper($org['president'] ?? '') }}</td>
                <td>{{ strtoupper($org['adviser'] ?? '') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <table style="border: none; width: 100%; border-collapse: collapse; margin-top: 1cm; font-family: Calibri, sans-serif;">
            <tr>
                <td style="border: none; width: 50%; vertical-align: top; padding-right: 20px;">
                    <div style="text-align: left; margin-bottom: 0.3cm;">Prepared by:</div>
                    <div style="text-align: left; margin-top: 1cm;">
                        <strong>{{ $preparedBy ?? 'DANIEL A. GEALONE' }}</strong><br>
                        <span class="signature-label">{{ $preparedByTitle ?? 'Secretary, OSAS' }}</span>
                    </div>
                </td>
                <td style="border: none; width: 50%; vertical-align: top; padding-left: 20px;">
                    <div style="text-align: left; margin-bottom: 0.3cm;">Noted by:</div>
                    <div style="text-align: left; margin-top: 1cm;">
                        <strong>{{ $notedBy ?? 'ALJON A. VILLAREAL' }}</strong><br>
                        <span class="signature-label">{{ $notedByTitle ?? 'Coordinator, Student Organization Unit' }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <div style="text-align: left; margin-top: 1.5cm; font-family: Calibri, sans-serif;">
            <div style="margin-bottom: 0.3cm;">Approved by:</div>
            <div style="margin-top: 1cm;">
                <strong>{{ $approvedBy ?? 'ALBERTO B. CASTILLO, EdD' }}</strong><br>
                <span class="signature-label">{{ $approvedByTitle ?? 'Director, OSAS' }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Times New Roman");
            $size = 10;
            $y = $pdf->get_height() - 40;
            
            // Right side - page numbering
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}: Recognized Student Organization";
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = $pdf->get_width() - $width - 40;
            $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0), 0);
        }
    </script>
    
    <div class="footer">
        <div class="footer-left">
            @if(file_exists(public_path('images/ISOlogo.jpg')))
            <img src="{{ public_path('images/ISOlogo.jpg') }}" alt="ISO Logo">
            @endif
        </div>
    </div>
</body>
</html>
