<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acquisitions Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { font-weight: bold; background-color: #f9fafb; }
    </style>
</head>
<body>
    <h1>Acquisitions Report</h1>
    <div class="meta">
        Generated: {{ $date->format('F d, Y h:i A') }}
        @if(!empty($searchTerm))
            | Filter: "{{ $searchTerm }}"
        @endif
        | Total Records: {{ $acquisitions->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ACQ #</th>
                <th>Txn #</th>
                <th>Catalog Title</th>
                <th>Vendor</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Total Cost</th>
                <th>Received</th>
            </tr>
        </thead>
        <tbody>
            @forelse($acquisitions as $acq)
                <tr>
                    <td>{{ $acq->acquisition_number }}</td>
                    <td>{{ $acq->transaction_number }}</td>
                    <td>{{ $acq->catalog->title ?? 'N/A' }}</td>
                    <td>{{ $acq->vendor->company_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $acq->quantity }}</td>
                    <td class="text-right">{{ number_format($acq->unit_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($acq->total_cost, 2) }}</td>
                    <td>{{ $acq->received_date ? $acq->received_date->format('M d, Y') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if($acquisitions->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">Grand Total:</td>
                    <td class="text-right">{{ number_format($acquisitions->sum('total_cost'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
