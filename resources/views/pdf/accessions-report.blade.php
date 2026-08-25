<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accessions Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .header { margin-bottom: 20px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Accessions Report</h2>
        <p>Generated on: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Accession #</th>
                <th>Batch #</th>
                <th>Title</th>
                <th>Call Number</th>
                <th>Condition</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accessions as $item)
                <tr>
                    <td>{{ $item->accession_number }}</td>
                    <td>{{ $item->batch_number }}</td>
                    <td>{{ $item->catalog->title ?? 'N/A' }}</td>
                    <td>{{ $item->call_number }}</td>
                    <td>{{ $item->condition }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
