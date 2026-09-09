<?php

namespace App\Exports;

use App\Models\Accession;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccessionNumberExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?string $search;
    protected ?string $statusFilter;
    protected ?string $startAccession;
    protected ?string $endAccession;

    public function __construct(string $search = '', string $statusFilter = '', ?string $startAccession = null, ?string $endAccession = null)
    {
        $symbols = '!@#$%^&*()_+`~{}[]|\\:;"\'<>,.?/';

        $this->search = trim($search, $symbols);
        $this->statusFilter = trim($statusFilter, $symbols);

        // Match database lowercase format
        $this->startAccession = !empty($startAccession) ? strtolower(trim($startAccession)) : null;
        $this->endAccession = !empty($endAccession) ? strtolower(trim($endAccession)) : null;
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Accession::query()
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('accession_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('batch_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('call_number', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->startAccession && $this->endAccession, function ($q) {
                $q->whereBetween('accession_number', [$this->startAccession, $this->endAccession]);
            })
            ->when($this->startAccession && !$this->endAccession, function ($q) {
                $q->where('accession_number', '>=', $this->startAccession);
            })
            ->when(!$this->startAccession && $this->endAccession, function ($q) {
                $q->where('accession_number', '<=', $this->endAccession);
            })
            ->orderBy('accession_number', 'asc');
    }

    public function headings(): array
    {
        return [
            'Accession #',
        ];
    }

    public function map($row): array
    {
        return [
            strtoupper($row->accession_number), // Keeps exported Excel text uppercase for readability
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1E3A8A'],
                ],
            ],
        ];
    }
}
