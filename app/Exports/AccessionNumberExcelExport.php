<?php

namespace App\Exports;

use App\Models\Accession;
use Illuminate\Support\Collection;
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

class AccessionNumberExcelExport extends DefaultValueBinder implements
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
        private readonly ?string $startAccession = null,
        private readonly ?string $endAccession = null,
    ) {}

    public function collection(): Collection
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $searchTerm = trim($this->search);
        $start = $this->startAccession ? strtoupper(trim($this->startAccession)) : null;
        $end = $this->endAccession ? strtoupper(trim($this->endAccession)) : null;

        return Accession::query()
            ->with(['acquisition.catalog'])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {
                $query->where(function ($q) use ($likeOperator, $searchTerm) {
                    $q->where('accession_number', $likeOperator, "%{$searchTerm}%")
                        ->orWhereHas('acquisition.catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($start !== null && $start !== '', fn ($query) => $query->where('accession_number', '>=', $start))
            ->when($end !== null && $end !== '', fn ($query) => $query->where('accession_number', '<=', $end))
            ->orderBy('accession_number', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Accession #',
        ];
    }

    /**
     * @param  Accession  $item
     */
    public function map($item): array
    {
        return [
            strtoupper((string) ($item->accession_number ?? '')),
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'A' && $value !== null && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
