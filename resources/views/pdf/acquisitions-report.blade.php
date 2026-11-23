{{-- resources/views/pdf/acquisitions-report.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BPS Library Management System — Acquisitions Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
            color: #1E293B;
            margin: 18px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #1E293B;
            margin: 0 0 4px 0;
        }
        .report-meta {
            font-size: 7.5px;
            color: #64748B;
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #EEF5FF;
            color: #1E293B;
            font-weight: bold;
            text-align: left;
            padding: 5px;
            border: 1px solid #CBD5E1;
            white-space: nowrap;
        }
        td {
            padding: 4.5px 5px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mono {
            font-family: DejaVu Sans Mono, monospace;
        }
        .upper {
            text-transform: uppercase;
        }
        .summary {
            margin-top: 8px;
            font-size: 8px;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <h1 class="report-title">BPS Library Management System — Acquisitions Report</h1>
    <p class="report-meta">
        Generated: {{ $date->format('Y-m-d H:i') }} | Total Records: {{ $acquisitions->count() }}
        @if($searchTerm !== '')
            | Search: "{{ $searchTerm }}"
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>ACQ #</th>
                <th>Txn #</th>
                <th>Status</th>
                <th>Delivery Type</th>
                <th>ISBN/ISSN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Asset Type</th>
                <th>Vendor</th>
                <th class="text-center">Ordered</th>
                <th class="text-center">Received</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Total Cost</th>
                <th>Expected Delivery</th>
                <th>Received Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandOrdered = 0;
                $grandReceived = 0;
                $grandTotalCost = 0;
            @endphp
            @forelse($acquisitions as $acq)
                @php
                    $authorName = $acq->catalog?->author?->name;
                    $assetTypeName = $acq->catalog?->assetType?->name;
                    $catalogTitle = $acq->catalog?->title;
                    $vendorName = $acq->vendor?->company_name;

                    $ord = (int) ($acq->quantity_ordered ?? 0);
                    $rec = (int) ($acq->quantity_received ?? 0);
                    $lineTotal = (float) ($acq->total_cost ?? 0);

                    $grandOrdered += $ord;
                    $grandReceived += $rec;
                    $grandTotalCost += $lineTotal;
                @endphp
                <tr>
                    <td class="mono upper">{{ $acq->acquisition_number }}</td>
                    <td class="mono upper">{{ $acq->transaction_number }}</td>
                    <td class="upper">{{ $acq->status }}</td>
                    <td class="upper">{{ $acq->delivery_type }}</td>
                    <td class="mono">{{ $acq->catalog?->isbn_issn ?? 'N/A' }}</td>
                    <td>{{ $catalogTitle ? Str::title((string) $catalogTitle) : '—' }}</td>
                    <td>{{ $authorName ? Str::title((string) $authorName) : 'N/A' }}</td>
                    <td>{{ $assetTypeName ? Str::title((string) $assetTypeName) : 'N/A' }}</td>
                    <td>{{ $vendorName ? Str::title((string) $vendorName) : '—' }}</td>
                    <td class="text-center">{{ $ord }}</td>
                    <td class="text-center">{{ $rec }}</td>
                    <td class="text-right mono">₱{{ number_format((float) $acq->unit_cost, 2) }}</td>
                    <td class="text-right mono">₱{{ number_format($lineTotal, 2) }}</td>
                    <td>{{ $acq->delivery_date ? $acq->delivery_date->format('M d, Y') : '—' }}</td>
                    <td>{{ $acq->received_date ? $acq->received_date->format('M d, Y') : '— (Pending)' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center">No acquisition records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Ordered: {{ $grandOrdered }} &nbsp;|&nbsp;
        Total Received: {{ $grandReceived }} &nbsp;|&nbsp;
        Grand Total Cost: ₱{{ number_format($grandTotalCost, 2) }}
    </div>
</body>
</html>
