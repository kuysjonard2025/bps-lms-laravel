<?php

namespace App\Exports;

use App\Models\Acquisition;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcquisitionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(private readonly string $search = '')
    {
    }

    public function query(): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $searchTerm = addcslashes(trim($this->search), '%_\\');

        return Acquisition::query()
            ->with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {
                $query->where(function ($q) use ($likeOperator, $searchTerm) {
                    $q->where('acquisition_number', $likeOperator, "%{$searchTerm}%")
                      ->orWhere('transaction_number', $likeOperator, "%{$searchTerm}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$searchTerm}%"))
                      ->orWhereHas('vendor', fn ($sub) => $sub->where('company_name', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Acquisition No.',
            'Transaction No.',
            'Catalog Title',
            'Author',
            'Asset Type',
            'Vendor',
            'Quantity',
            'Unit Cost',
            'Total Cost',
            'Received Date',
            'Remarks',
        ];
    }

    public function map($acquisition): array
    {
        return [
            $acquisition->acquisition_number,
            $acquisition->transaction_number,
            $acquisition->catalog->title ?? 'N/A',
            $acquisition->catalog->author->name ?? 'N/A',
            $acquisition->catalog->assetType->name ?? 'N/A',
            $acquisition->vendor->company_name ?? 'N/A',
            $acquisition->quantity,
            (float) $acquisition->unit_cost,
            (float) $acquisition->total_cost,
            $acquisition->received_date ? $acquisition->received_date->format('Y-m-d') : '',
            $acquisition->remarks,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
