<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan of Activities Report</title>
    <style>
        @page {
            size: legal landscape;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 1.5cm;
            margin-right: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Ensure page breaks work properly for large tables */
        tbody tr {
            page-break-inside: avoid;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }

        .header {
            text-align: center;
            font-size: 13px;
            margin: 0 0 0.5cm 0;
            padding-top: 0.3cm;
            position: relative;
        }

        .logo {
            position: absolute;
            top: calc(-0.5cm);
            left: -2cm;
            width: 250px;
            height: auto;
        }

        .university-name {
            max-width: 23%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }

        .calibri-text {
            font-family: Calibri, sans-serif;
        }

        .meta-info {
            margin: 15px 0;
            font-size: 11pt;
            font-family: 'Times New Roman', serif;
        }

        .meta-info p {
            margin: 3px 0;
        }

        .filters-section {
            margin: 10px 0;
            font-size: 9pt;
        }

        .filters-section strong {
            display: block;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11pt;
            font-family: 'Times New Roman', serif;
        }

        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            font-size: 11pt;
            font-family: 'Times New Roman', serif;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        td {
            word-wrap: break-word;
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 11pt;
            font-family: 'Times New Roman', serif;
        }

        p {
            margin: 3px 0;
            word-wrap: break-word;
            line-height: 1.15;
        }
        
        /* Prevent text overflow in cells */
        td, th {
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/lspu-logo.png')))
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        @endif
        <span class="calibri-text" style="font-size:11pt; font-family:Calibri, Arial, sans-serif;">Republic of the Philippines</span><br>
        @if(file_exists(public_path('images/lspu-name.png')))
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name" style="font-family:'Old English Text MT', 'Book Antiqua', serif; font-size:12pt;"><br>
        @else
        <strong style="font-size:12pt; font-family:'Old English Text MT', 'Book Antiqua', serif;">LAGUNA STATE POLYTECHNIC UNIVERSITY</strong><br>
        @endif
        <span class="calibri-text" style="font-size:11pt; font-family:Calibri, Arial, sans-serif;">Province of Laguna</span><br>
        <br>
        <strong style="font-family:'Times New Roman', serif; font-size:11pt;">OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
        <br>
        <span class="sub-header" style="font-family:'Times New Roman', serif; font-size:11pt;"><strong>PLAN OF ACTIVITIES REPORT</strong></span>
    </div>

    <div class="meta-info">
        <p><strong>Generated:</strong> {{ $generatedDate }}</p>
        <p><strong>By:</strong> {{ $generatedBy }}</p>
        <p><strong>Total Activities:</strong> {{ count($activities) }}</p>
    </div>

    @php
        $hasFilters = !empty($filters['search']) || 
                     (!empty($filters['status']) && $filters['status'] !== 'all') || 
                     !empty($filters['organization']) ||
                     (!empty($filters['columnFilters']) && collect($filters['columnFilters'])->some(fn($f) => !empty($f['value'])));
    @endphp

    @if($hasFilters)
    <div class="filters-section">
        <strong>Active Filters:</strong>
        @if(!empty($filters['search']))
            <p>Search: "{{ $filters['search'] }}"</p>
        @endif
        @if(!empty($filters['status']) && $filters['status'] !== 'all')
            <p>Status: {{ ucfirst($filters['status']) }}</p>
        @endif
        @if(!empty($filters['organization']))
            <p>Organization: {{ $filters['organization'] }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @if($isAdmin)
                <th style="width: 12%;">Organization</th>
                @endif
                <th style="width: {{ $isAdmin ? '14%' : '16%' }};">Objective</th>
                <th style="width: {{ $isAdmin ? '13%' : '14%' }};">Activity</th>
                <th style="width: {{ $isAdmin ? '16%' : '18%' }};">Description</th>
                <th style="width: {{ $isAdmin ? '11%' : '12%' }};">Persons Involved</th>
                <th style="width: {{ $isAdmin ? '9%' : '10%' }};">Target Date</th>
                <th style="width: {{ $isAdmin ? '9%' : '10%' }};">Budget</th>
                <th style="width: {{ $isAdmin ? '8%' : '10%' }};" class="text-center">Participants</th>
                <th style="width: {{ $isAdmin ? '8%' : '10%' }};" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
            <tr>
                @if($isAdmin)
                <td>{{ $activity['organization'] }}</td>
                @endif
                <td>{{ $activity['objective'] }}</td>
                <td>{{ $activity['activity_name'] }}</td>
                <td>{{ $activity['description'] }}</td>
                <td>{{ $activity['persons_involved'] }}</td>
                <td class="text-center">{{ $activity['target_date_formatted'] }}</td>
                <td class="text-center">
                    @if($activity['budget'] && $activity['budget'] !== 'N/A')
                        PHP {{ number_format($activity['budget'], 2) }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-center">{{ $activity['target_participants'] }}</td>
                <td class="text-center">{{ $activity['status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center">
                    No activities found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Laguna State Polytechnic University © {{ date('Y') }} | Office of Student Affairs and Services</p>
    </div>
</body>
</html>
