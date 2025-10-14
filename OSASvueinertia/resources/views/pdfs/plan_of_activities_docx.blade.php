<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office' 
      xmlns:w='urn:schemas-microsoft-com:office:word' 
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Microsoft Word 15">
    <meta name="Originator" content="Microsoft Word 15">
    <title>Plan of Activities Report</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 14in 8.5in;  /* Legal landscape */
            margin: 0.5in 0.75in;
            mso-page-orientation: landscape;
        }
        
        div.Section1 {
            page: Section1;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        
        .header-text {
            text-align: center;
            margin: 0;
        }
        
        .header-text .republic {
            font-size: 11pt;
            font-family: Calibri, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-weight: normal;
            line-height: 1.2;
        }
        
        .header-text .university-name {
            font-size: 12pt;
            font-family: 'Old English Text MT', 'Book Antiqua', serif;
            margin: 2px 0;
            padding: 0;
            font-weight: normal;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        
        .header-text .province {
            font-size: 11pt;
            font-family: Calibri, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-weight: normal;
            line-height: 1.2;
        }
        
        .header h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 10px 0 2px 0;
            font-family: 'Times New Roman', serif;
            text-transform: uppercase;
        }
        
        .header h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0 15px 0;
            font-family: 'Times New Roman', serif;
            text-transform: uppercase;
        }
        
        .metadata {
            text-align: left;
            font-size: 11pt;
            margin-bottom: 15px;
            font-family: 'Times New Roman', serif;
        }
        
        .metadata-item {
            margin: 2px 0;
            font-family: 'Times New Roman', serif;
        }
        
        table.activities-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin: 0;
            font-family: 'Times New Roman', serif;
        }
        
        table.activities-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            padding: 8px 5px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 11pt;
            font-family: 'Times New Roman', serif;
        }
        
        table.activities-table td {
            padding: 6px 5px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.2;
            font-family: 'Times New Roman', serif;
        }
        
        .status {
            font-weight: bold;
            text-align: center;
            font-family: 'Times New Roman', serif;
        }
        
        .footer {
            text-align: center;
            font-size: 8pt;
            font-style: italic;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-family: 'Times New Roman', serif;
        }
        
        .currency {
            text-align: right;
            white-space: nowrap;
        }
        
        .center {
            text-align: center;
        }
        
        /* Prevent page breaks inside table rows */
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
<div class="Section1">
    <!-- Header -->
    <div class="header">
        <div class="header-text">
            <div class="republic">Republic of the Philippines</div>
            <div class="university-name">Laguna State Polytechnic University</div>
            <div class="province">Province of Laguna</div>
        </div>
        <h2>Office of Student Affairs and Services</h2>
        <h3>Plan of Activities Report</h3>
    </div>
    
    <!-- Metadata -->
    <div class="metadata">
        <div class="metadata-item">
            <strong>Generated:</strong> {{ $generatedDate }} {{ date('h:i A') }}
        </div>
        <div class="metadata-item">
            <strong>Generated By:</strong> {{ $generatedBy }}
        </div>
        <div class="metadata-item">
            <strong>Total Activities:</strong> {{ count($activities) }}
        </div>
    </div>
    
    <!-- Activities Table -->
    <table class="activities-table">
        <thead>
            <tr>
                @if($isAdmin)
                    <th style="width: 12%;">Organization</th>
                @endif
                <th style="width: 12%;">Objective</th>
                <th style="width: 12%;">Activity</th>
                <th style="width: 15%;">Brief Description</th>
                <th style="width: 12%;">Persons Involved</th>
                <th style="width: 8%;">Target Date</th>
                <th style="width: 10%;">Budget</th>
                <th style="width: 8%;">Target Participants</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    @if($isAdmin)
                        <td>{{ $activity['organization'] }}</td>
                    @endif
                    <td>{{ $activity['objective'] }}</td>
                    <td><strong>{{ $activity['activity_name'] }}</strong></td>
                    <td>{{ $activity['description'] }}</td>
                    <td>{{ $activity['persons_involved'] }}</td>
                    <td class="center">{{ $activity['target_date_formatted'] }}</td>
                    <td class="currency">
                        @if($activity['budget'] == 0 || $activity['budget'] == 'N/A')
                            N/A
                        @else
                            ₱{{ number_format($activity['budget'], 2) }}
                        @endif
                    </td>
                    <td class="center">{{ $activity['target_participants'] }}</td>
                    <td class="status status-{{ strtolower($activity['status']) }}">
                        {{ $activity['status'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" class="center">
                        <em>No activities found</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        This report was generated by {{ $generatedBy }} on {{ $generatedDate }} {{ date('h:i A') }}
    </div>
</div>
</body>
</html>
