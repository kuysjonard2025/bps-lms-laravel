<?php // app/Exports/CatalogsExcelExport.php

namespace App\Exports;

use App\Models\Catalog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CatalogsExcelExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly string $search = '',
    ) {}

    public function collection(): Collection
    {
        return Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
            ->when(trim($this->search) !== '', fn ($query) => $query->search(trim($this->search)))
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Call #',
            'ISBN',
            'Title',
            'Author',
            'Publisher',
            'Reference',
            'Asset Type',
            'Copyright Year',
            'Edition',
        ];
    }

    /**
     * @param  Catalog  $catalog
     */
    public function map($catalog): array
    {
        $authorName = $catalog->author?->name;
        if (! $authorName) {
            $authorName = '— (Periodical)';
        }

        return [
            (string) ($catalog->call_number ?? ''),
            (string) ($catalog->isbn_issn ?? ''),
            (string) $catalog->title,
            (string) $authorName,
            (string) ($catalog->publisher?->name ?? ''),
            (string) ($catalog->generalReference?->name ?? ''),
            (string) ($catalog->assetType?->name ?? ''),
            (string) ($catalog->publication_year ?? ''),
            (string) ($catalog->edition ?: 'N/A'),
        ];
    }
}
