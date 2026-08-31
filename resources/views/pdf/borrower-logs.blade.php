<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Borrower Attendance Logs Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; color: #111; }
        .header p { margin: 4px 0 0; color: #666; font-size: 10px; }
        .meta { margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; color: #475569; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-green { background-color: #dcfce7; color: #15803d; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Borrower Attendance Logs Report</h2>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="meta">
        <strong>Filter Date:</strong> {{ $filterDate ? \Carbon\Carbon::parse($filterDate)->format('M d, Y') : 'All Dates' }} |
        <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $filterStatus)) }} |
        <strong>Total Records:</strong> {{ count($logs) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Borrower ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Grade & Section</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</td>
                    <td>{{ $log->patron->school_id ?? 'N/A' }}</td>
                    <td>
                        {{ ucwords($log->patron->first_name) ?? '' }}
                        {{ ucwords($log->patron->middle_name) ?? '' }}
                        {{ ucwords($log->patron->last_name) ?? 'Deleted Borrower' }}
                        {{ blank($log->patron->suffix) ? '' : ' ' . ucwords($log->patron->suffix) }}
                    </td>
                    <td>{{ ucwords($log->patron->patronType->name) ?? 'N/A' }}</td>
                    <td>
                        @if($log->patron && $log->patron->gradeLevel)
                            {{ ucwords($log->patron->gradeLevel->name) }} - {{ ucwords($log->patron->section->name) ?? '' }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($log->time_in)->format('h:i:s A') }}</td>
                    <td>{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i:s A') : '--:--:--' }}</td>
                    <td>
                        @if($log->time_out)
                            <span class="badge badge-blue">Logged Out</span>
                        @else
                            <span class="badge badge-green">Inside Library</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #999;">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
