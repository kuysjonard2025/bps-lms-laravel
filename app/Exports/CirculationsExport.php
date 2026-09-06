<?php

namespace App\Exports;

use App\Models\Circulation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CirculationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomStartCell, WithEvents
{
    use Exportable;

    public string $search;
    public string $filterStatus;

    public function __construct(string $search = '', string $filterStatus = 'all')
    {
        $this->search = $search;
        $this->filterStatus = $filterStatus;
    }

    /**
     * Start table headers at Row 5 to make room for meta header.
     */
    public function startCell(): string
    {
        return 'A5';
    }

    public function query(): Builder|QueryBuilder|Relation
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Circulation::query()
            ->with(['patron', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', fn (Builder $q) => $q->whereIn('status', ['borrowed', 'overdue']))
            ->when($this->filterStatus === 'returned', fn (Builder $q) => $q->whereIn('status', ['returned', 'lost', 'minor', 'severe']))
            ->when($this->filterStatus === 'overdue', function (Builder $q) {
                $q->where('status', 'overdue')
                  ->orWhere(function (Builder $sub) {
                      $sub->whereNull('returned_at')->where('due_at', '<', now());
                  });
            })
            ->when($this->search, function (Builder $q) use ($likeOperator) {
                $q->where(function (Builder $query) use ($likeOperator) {
                    $query->whereHas('patron', function (Builder $patronQ) use ($likeOperator) {
                        $patronQ->where('school_id', $likeOperator, "%{$this->search}%")
                            ->orWhere('rfid_tag', $likeOperator, "%{$this->search}%")
                            ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('last_name', $likeOperator, "%{$this->search}%");
                    })
                    ->orWhereHas('accession', function (Builder $accQ) use ($likeOperator) {
                        $accQ->where('accession_number', $likeOperator, "%{$this->search}%")
                            ->orWhereHas('catalog', fn (Builder $catQ) => $catQ->where('title', $likeOperator, "%{$this->search}%"));
                    })
                    ->orWhereHas('user', function (Builder $userQ) use ($likeOperator) {
                        $userQ->where('name', $likeOperator, "%{$this->search}%")
                            ->orWhere('email', $likeOperator, "%{$this->search}%");
                    });
                });
            })
            ->latest('borrowed_at');
    }

    public function headings(): array
    {
        return [
            'Accession #',
            'Book Title',
            'Student/Employee #',
            'Borrower Name',
            'Borrowed At',
            'Due At',
            'Receipt #',
            'Fine Amount',
            'Condition',
            'Status',
            'Processed By',
        ];
    }

    public function map($loan): array
    {
        $borrowerName = trim(implode(' ', array_filter([
            $loan->patron?->first_name,
            $loan->patron?->middle_name,
            $loan->patron?->last_name,
            $loan->patron?->suffix,
        ])));

        $processedBy = method_exists($loan->user, 'getFullNameAttribute')
            ? $loan->user?->getFullNameAttribute()
            : ($loan->user?->name ?? 'System User');

        $title = $loan->accession?->catalog?->title;

        return [
            $loan->accession?->accession_number ?? 'N/A',
            $title ? ucwords(strtolower($title)) : 'N/A',
            $loan->patron?->school_id ?? 'N/A',
            $borrowerName ? ucwords(strtolower($borrowerName)) : 'N/A',
            $loan->borrowed_at ? $loan->borrowed_at->format('M d, Y h:i A') : '-',
            $loan->due_at ? $loan->due_at->format('M d, Y') : '-',
            $loan->receipt_number ?? '-',
            (float) ($loan->fine_amount ?? 0),
            ucfirst($loan->condition ?? 'Good'),
            ucfirst($loan->status ?? 'N/A'),
            $processedBy ? ucwords(strtolower($processedBy)) : 'System User',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => '"₱"#,##0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $generatedBy = auth()->user()?->getFullNameAttribute() ?? 'System User';
                $searchLabel = $this->search ? $this->search : 'All (None applied)';
                $statusLabel = ucfirst($this->filterStatus ?? 'All Statuses');

                // Row 1: Main Title
                $sheet->setCellValue('A1', 'BPS LIBRARY MANAGEMENT SYSTEM - CIRCULATION REPORT');

                // Row 2: Metadata Left & Right
                $sheet->setCellValue('A2', 'Generated On: ' . now()->format('F d, Y g:i A'));
                $sheet->setCellValue('E2', 'Generated By: ' . $generatedBy);

                // Row 3: Metadata Left & Right
                $sheet->setCellValue('A3', 'Search Query: ' . $searchLabel);
                $sheet->setCellValue('E3', 'Status Filter: ' . $statusLabel);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Title Styling (Row 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'italic' => true,
                    'color' => ['rgb' => '000000'],
                ],
            ],

            // Meta Details Styling (Rows 2 & 3)
            2 => [
                'font' => [
                    'italic' => true,
                    'size' => 9,
                    'color' => ['rgb' => '333333'],
                ],
            ],
            3 => [
                'font' => [
                    'italic' => true,
                    'size' => 9,
                    'color' => ['rgb' => '333333'],
                ],
            ],

            // Table Header Styling (Row 5 - Dark Blue Bar matching screenshot)
            5 => [
                'font' => [
                    'bold' => true,
                    'size' => 9,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10257F'], // Dark Navy Accent
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
