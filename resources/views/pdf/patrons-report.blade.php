{{-- resources/views/pdf/patrons-report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patrons Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1e293b; }
        h1 { font-size: 15px; margin: 0 0 4px 0; }
        p.meta { font-size: 9px; color: #64748b; margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <h1>BPS Library Management System — Registered Patrons Report</h1>
    <p class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
        @if (trim($search) !== '')
            | Search Filter: "{{ $search }}"
        @endif
        | Total Records: {{ $patrons->count() }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Student/Employee #</th>
                <th>RFID Tag</th>
                <th>Full Name</th>
                <th>Type</th>
                <th>Department</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Adviser</th>
                <th>Strand</th>
                <th>Contact #</th>
                <th>Email</th>
                <th>Address</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patrons as $patron)
                @php
                    $adviserName = $patron->adviser?->full_name
                        ?? $patron->adviser?->name
                        ?? trim(($patron->adviser?->last_name ? $patron->adviser->last_name . ', ' : '')
                            . ($patron->adviser?->first_name ?? '') . ' '
                            . ($patron->adviser?->middle_name ?? '') . ' '
                            . ($patron->adviser?->suffix ?? ''));
                @endphp
                <tr>
                    <td>{{ strtoupper($patron->school_id) }}</td>
                    <td>{{ strtoupper($patron->rfid_tag) }}</td>
                    <td>{{ \Illuminate\Support\Str::title(trim("{$patron->last_name}, {$patron->first_name} {$patron->middle_name} {$patron->suffix}")) }}</td>
                    <td>{{ $patron->patronType?->name ? strtoupper($patron->patronType->name) : 'N/A' }}</td>
                    <td>{{ $patron->department?->name ? strtoupper($patron->department->name) : '—' }}</td>
                    <td>{{ $patron->gradeLevel?->name ?? '—' }}</td>
                    <td>{{ $patron->section?->name ?? '—' }}</td>
                    <td>{{ $adviserName ? \Illuminate\Support\Str::title($adviserName) : '—' }}</td>
                    <td>{{ $patron->strand?->code ? strtoupper($patron->strand->code) : ($patron->strand?->name ?? '—') }}</td>
                    <td>{{ $patron->contact_number }}</td>
                    <td>{{ $patron->email ?: 'N/A' }}</td>
                    <td>{{ $patron->address ? \Illuminate\Support\Str::title($patron->address) : 'N/A' }}</td>
                    <td>{{ strtoupper($patron->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13">No patron records match the current search.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
