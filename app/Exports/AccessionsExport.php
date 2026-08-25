<?php

namespace App\Exports;

use App\Models\Accession;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AccessionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected string $search;
    protected string $statusFilter;

    public function __construct(string $search = '', string $statusFilter = '')
    {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Accession::with(['catalog.author', 'catalog.assetType', 'acquisition'])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('accession_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('batch_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('call_number', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Accession #',
            'Batch #',
            'Catalog Title',
            'Acquisition #',
            'Call Number',
            'Condition',
            'Status',
            'Acquired Date',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->accession_number,
            $row->batch_number,
            $row->catalog->title ?? 'N/A',
            $row->acquisition->acquisition_number ?? 'N/A',
            $row->call_number,
            $row->condition,
            $row->status,
            $row->acquired_date ? Carbon::parse($row->acquired_date)->format('Y-m-d') : 'N/A',
            $row->remarks ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the header row (Row 1)
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'], // Primary blue header
                ],
            ],
        ];
    }
}
