<?php

namespace App\Exports;

use App\Models\Accession;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AccessionsExcelExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithCustomValueBinder,
    ShouldAutoSize
{
    public function __construct(
        private readonly string $search = '',
        private readonly string $statusFilter = '',
    ) {}

    public function collection(): Collection
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $searchTerm = trim($this->search);

        return Accession::query()
            ->with([
                'acquisition.vendor',
                'acquisition.catalog.author',
                'acquisition.catalog.publisher',
                'acquisition.catalog.generalReference',
                'acquisition.catalog.assetType',
            ])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {$query->where(function ($q) use ($likeOperator, $searchTerm) {$q->where('accession_number', $likeOperator, "\%{$searchTerm}%")
                        ->orWhereHas('acquisition.catalog', fn ($sub) =>$sub->where('title', $likeOperator, "\%{$searchTerm}%"));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status',$this->statusFilter))
            ->orderBy('accession_number', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Accession #',
            'ISBN/ISSN',
            'Title',
            'Author',
            'Publisher',
            'Gen Reference',
            'Asset Type',
            'Call Number',
            'ACQ Ref',
            'Vendor',
            'Condition',
            'Status',
            'Remarks',
        ];
    }

    /**
     * @param  Accession  $item
     */
    public function map($item): array
    {
        $catalog =$item->acquisition?->catalog;
        $vendor =$item->acquisition?->vendor;

        return [
            strtoupper((string) ($item->accession_number ?? '')),
            (string) ($catalog?->isbn_issn ?? 'N/A'),
            isset($catalog?->title) ? Str::title((string) $catalog->title) : '—',
            isset($catalog?->author?->name) ? Str::title((string) $catalog->author->name) : 'N/A',
            isset($catalog?->publisher?->name) ? Str::title((string) $catalog->publisher->name) : 'N/A',
            isset($catalog?->generalReference?->name) ? Str::title((string) $catalog->generalReference->name) : 'N/A',
            isset($catalog?->assetType?->name) ? Str::title((string) $catalog->assetType->name) : 'N/A',
            strtoupper((string) ($catalog?->call_number ?? 'N/A')),
            strtoupper((string) ($item->acquisition?->acquisition_number ?? '—')),
            isset($vendor?->company_name) ? Str::title((string) $vendor->company_name) : 'N/A',
            Str::title((string) ($item->condition ?? '')),
            Str::title((string) ($item->status ?? '')),
            (string) ($item->remarks ?? ''),
        ];
    }

    public function bindValue(Cell $cell,$value): bool
    {
        if (in_array($cell->getColumn(), ['A', 'B', 'H', 'I'], true) && $value !== null &&$value !== '') {
            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell,$value);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
