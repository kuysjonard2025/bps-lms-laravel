{{-- resources/views/pdf/catalogs-report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catalogs Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        p.meta { font-size: 9px; color: #64748b; margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <h1>BPS Library Management System — Catalogs Report</h1>
    <p class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
        @if ($search !== '')
            | Search Filter: "{{ $search }}"
        @endif
        | Total Records: {{ $catalogs->count() }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Call #</th>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Reference</th>
                <th>Asset Type</th>
                <th>Year</th>
                <th>Edition</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($catalogs as $catalog)
                <tr>
                    <td>{{ $catalog->call_number }}</td>
                    <td>{{ $catalog->isbn_issn }}</td>
                    <td>{{ $catalog->title }}</td>
                    <td>{{ $catalog->author?->name ?? '— (Periodical)' }}</td>
                    <td>{{ $catalog->publisher?->name }}</td>
                    <td>{{ $catalog->generalReference?->name }}</td>
                    <td>{{ $catalog->assetType?->name }}</td>
                    <td>{{ $catalog->publication_year }}</td>
                    <td>{{ $catalog->edition ?: 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No catalog records match the current filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
