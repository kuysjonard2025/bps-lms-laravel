<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accessions Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 15px; margin: 0 0 4px 0; }
        p.meta { font-size: 8.5px; color: #64748b; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
    </style>
</head>
<body>
    <h1>BPS Library Management System — Accessions Report</h1>
    <p class="meta">
        Generated: {{ $date }}
        @if (!empty($filters['search']))
            | Search: "{{ $filters['search'] }}"
        @endif
        @if (!empty($filters['status']))
            | Status: {{ \Illuminate\Support\Str::title($filters['status']) }}
        @endif
        | Total Records: {{ $accessions->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Accession #</th>
                <th>ISBN/ISSN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Call #</th>
                <th>ACQ Ref</th>
                <th>Vendor</th>
                <th>Condition</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accessions as $item)
                @php
                    $catalog = $item->acquisition?->catalog;
                    $vendor = $item->acquisition?->vendor;
                @endphp
                <tr>
                    <td class="mono">{{ strtoupper($item->accession_number) }}</td>
                    <td class="mono">{{ $catalog?->isbn_issn ?? 'N/A' }}</td>
                    <td>{{ isset($catalog?->title) ? \Illuminate\Support\Str::title($catalog->title) : '—' }}</td>
                    <td>{{ isset($catalog?->author?->name) ? \Illuminate\Support\Str::title($catalog->author->name) : 'N/A' }}</td>
                    <td class="mono">{{ strtoupper($catalog?->call_number ?? 'N/A') }}</td>
                    <td class="mono">{{ strtoupper($item->acquisition?->acquisition_number ?? '—') }}</td>
                    <td>{{ isset($vendor?->company_name) ? \Illuminate\Support\Str::title($vendor->company_name) : 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::title($item->condition) }}</td>
                    <td>{{ \Illuminate\Support\Str::title($item->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No accession records match the current filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
