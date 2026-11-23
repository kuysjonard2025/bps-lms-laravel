<?php

// app/Exports/AcquisitionsExcelExport.php

namespace App\Exports;

use App\Models\Acquisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcquisitionsExcelExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly string $search = ''
    ) {}

    private function query(): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $searchTerm = strtolower(mb_convert_encoding(addcslashes(trim($this->search), '%_\\'), 'UTF-8', 'UTF-8'));

        return Acquisition::with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {
                $query->where(function ($q) use ($likeOperator, $searchTerm) {
                    $q->where('acquisition_number', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('transaction_number', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('status', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('delivery_type', $likeOperator, "%{$searchTerm}%")
                        ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('catalog.author', fn ($sub) => $sub->where('name', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('catalog.assetType', fn ($sub) => $sub->where('name', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('vendor', fn ($sub) => $sub->where('company_name', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->latest();
    }

    public function collection(): Collection
    {
        return $this->query()->get();
    }

    public function headings(): array
    {
        return [
            'ACQ #',
            'Txn #',
            'Status',
            'Delivery Type',
            'ISBN/ISSN',
            'Title',
            'Author',
            'Asset Type',
            'Vendor',
            'Ordered',
            'Received',
            'Unit Cost',
            'Total Cost',
            'Expected Delivery',
            'Received Date',
        ];
    }

    /**
     * @param Acquisition $acquisition
     */
    public function map($acquisition): array
    {
        $authorName = $acquisition->catalog?->author?->name;
        $assetTypeName = $acquisition->catalog?->assetType?->name;
        $catalogTitle = $acquisition->catalog?->title;
        $vendorName = $acquisition->vendor?->company_name;

        return [
            strtoupper((string) ($acquisition->acquisition_number ?? '')),
            strtoupper((string) ($acquisition->transaction_number ?? '')),
            strtoupper((string) ($acquisition->status ?? '')),
            strtoupper((string) ($acquisition->delivery_type ?? '')),
            (string) ($acquisition->catalog?->isbn_issn ?? 'N/A'),
            $catalogTitle ? Str::title((string) $catalogTitle) : '—',
            $authorName ? Str::title((string) $authorName) : 'N/A',
            $assetTypeName ? Str::title((string) $assetTypeName) : 'N/A',
            $vendorName ? Str::title((string) $vendorName) : '—',
            (int) ($acquisition->quantity_ordered ?? 0),
            (int) ($acquisition->quantity_received ?? 0),
            '₱' . number_format((float) ($acquisition->unit_cost ?? 0), 2),
            '₱' . number_format((float) ($acquisition->total_cost ?? 0), 2),
            $acquisition->delivery_date ? $acquisition->delivery_date->format('M d, Y') : '—',
            $acquisition->received_date ? $acquisition->received_date->format('M d, Y') : '— (Pending)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEF5FF'],
                ],
            ],
        ];
    }
}
