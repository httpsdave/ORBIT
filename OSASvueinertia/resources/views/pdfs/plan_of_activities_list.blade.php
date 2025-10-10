<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan of Activities Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #1a1a1a;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            font-size: 9px;
        }

        .meta-info div {
            display: inline-block;
        }

        .meta-info strong {
            color: #1e3a8a;
        }

        .filters-applied {
            margin-bottom: 12px;
            padding: 8px 12px;
            background-color: #eff6ff;
            border-radius: 4px;
            font-size: 8px;
        }

        .filters-applied strong {
            color: #1e40af;
            display: block;
            margin-bottom: 4px;
        }

        .filter-tag {
            display: inline-block;
            padding: 2px 8px;
            margin-right: 6px;
            margin-bottom: 4px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 3px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }

        thead {
            background: linear-gradient(to bottom, #1e3a8a, #1e40af);
            color: white;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        th:last-child {
            border-right: none;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #f1f5f9;
        }

        td {
            padding: 7px 6px;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-disapproved {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .date-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            margin-top: 2px;
        }

        .date-upcoming {
            background-color: #dcfce7;
            color: #166534;
        }

        .date-past {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .currency {
            font-weight: 600;
            color: #059669;
        }

        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
            color: #1e3a8a;
        }

        /* Column widths for optimal layout */
        @if($isAdmin)
        .col-org { width: 12%; }
        .col-objective { width: 14%; }
        .col-activity { width: 13%; }
        .col-description { width: 16%; }
        .col-persons { width: 11%; }
        .col-date { width: 9%; }
        .col-budget { width: 9%; }
        .col-participants { width: 8%; }
        .col-status { width: 8%; }
        @else
        .col-objective { width: 16%; }
        .col-activity { width: 14%; }
        .col-description { width: 18%; }
        .col-persons { width: 12%; }
        .col-date { width: 10%; }
        .col-budget { width: 10%; }
        .col-participants { width: 10%; }
        .col-status { width: 10%; }
        @endif

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 PLAN OF ACTIVITIES REPORT</h1>
        <div class="subtitle">Laguna State Polytechnic University - Office of Student Affairs and Services</div>
        <div class="subtitle">{{ $isAdmin ? 'Administrator View' : 'Organization View' }}</div>
    </div>

    <div class="meta-info">
        <div><strong>Generated:</strong> {{ $generatedDate }}</div>
        <div><strong>By:</strong> {{ $generatedBy }}</div>
        <div><strong>Total Activities:</strong> {{ count($activities) }}</div>
    </div>

    @php
        $hasFilters = !empty($filters['search']) || 
                     (!empty($filters['status']) && $filters['status'] !== 'all') || 
                     !empty($filters['organization']) ||
                     (!empty($filters['columnFilters']) && collect($filters['columnFilters'])->some(fn($f) => !empty($f['value'])));
    @endphp

    @if($hasFilters)
    <div class="filters-applied">
        <strong>🔍 Active Filters:</strong>
        @if(!empty($filters['search']))
            <span class="filter-tag">Search: "{{ $filters['search'] }}"</span>
        @endif
        @if(!empty($filters['status']) && $filters['status'] !== 'all')
            <span class="filter-tag">Status: {{ ucfirst($filters['status']) }}</span>
        @endif
        @if(!empty($filters['organization']))
            <span class="filter-tag">Organization: {{ $filters['organization'] }}</span>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @if($isAdmin)
                <th class="col-org">Organization</th>
                @endif
                <th class="col-objective">Objective</th>
                <th class="col-activity">Activity</th>
                <th class="col-description">Description</th>
                <th class="col-persons">Persons Involved</th>
                <th class="col-date">Target Date</th>
                <th class="col-budget">Budget</th>
                <th class="col-participants">Participants</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
            <tr>
                @if($isAdmin)
                <td class="col-org">
                    <div class="text-truncate">{{ $activity['organization'] }}</div>
                </td>
                @endif
                <td class="col-objective">
                    <div class="text-truncate">{{ $activity['objective'] }}</div>
                </td>
                <td class="col-activity">
                    <strong>{{ $activity['activity_name'] }}</strong>
                </td>
                <td class="col-description">
                    <div class="text-truncate">{{ $activity['description'] }}</div>
                </td>
                <td class="col-persons">
                    <div class="text-truncate">{{ $activity['persons_involved'] }}</div>
                </td>
                <td class="col-date">
                    {{ $activity['target_date_formatted'] }}
                    @php
                        $isPast = \Carbon\Carbon::parse($activity['target_date'])->isPast();
                    @endphp
                    <div class="date-badge {{ $isPast ? 'date-past' : 'date-upcoming' }}">
                        {{ $isPast ? 'Past' : 'Upcoming' }}
                    </div>
                </td>
                <td class="col-budget">
                    @if($activity['budget'] && $activity['budget'] !== 'N/A')
                        <span class="currency">₱{{ number_format($activity['budget'], 2) }}</span>
                    @else
                        N/A
                    @endif
                </td>
                <td class="col-participants" style="text-align: center;">
                    {{ $activity['target_participants'] }}
                </td>
                <td class="col-status">
                    @php
                        $statusClass = 'status-' . strtolower($activity['status']);
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ $activity['status'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isAdmin ? 9 : 8 }}" style="text-align: center; padding: 30px; color: #64748b;">
                    No activities found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($activities) > 0)
    <div class="summary">
        📊 Total: {{ count($activities) }} {{ Str::plural('Activity', count($activities)) }} 
        @php
            $approved = collect($activities)->where('status', 'Approved')->count();
            $pending = collect($activities)->where('status', 'Pending')->count();
            $disapproved = collect($activities)->where('status', 'Disapproved')->count();
        @endphp
        | ✅ Approved: {{ $approved }} | ⏳ Pending: {{ $pending }} | ❌ Disapproved: {{ $disapproved }}
    </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Laguna State Polytechnic University © {{ date('Y') }} | Office of Student Affairs and Services</p>
    </div>
</body>
</html>
