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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CirculationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting,WithCustomStartCell, WithEvents
{
    use Exportable;

    public string $search;
    public string $filterStatus;

    public function __construct(string $search = '', string $filterStatus = 'borrowed')
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
            ->with(['patron.patronType', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', fn ($q) => $q->where('status', 'borrowed'))
            ->when($this->filterStatus === 'returned', fn ($q) => $q->where('status', 'returned'))
            ->when($this->filterStatus === 'overdue', fn ($q) => $q->where('status', 'borrowed')->where('due_at', '<', now()))
            ->when($this->search, function ($q) use ($likeOperator) {
                $searchTerm = "%{$this->search}%";
                $q->where(function ($query) use ($likeOperator, $searchTerm) {
                    $query->where('receipt_number', $likeOperator, $searchTerm)
                        ->orWhereHas('patron', function ($patronQ) use ($likeOperator, $searchTerm) {
                            $patronQ->where('school_id', $likeOperator, $searchTerm)
                                ->orWhere('first_name', $likeOperator, $searchTerm)
                                ->orWhere('last_name', $likeOperator, $searchTerm);
                        })->orWhereHas('accession', function ($accQ) use ($likeOperator, $searchTerm) {
                            $accQ->where('accession_number', $likeOperator, $searchTerm)
                                ->orWhereHas('catalog', fn ($catQ) => $catQ->where('title', $likeOperator, $searchTerm));
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
            'Name',
            'Type',
            'Borrowed Date',
            'Due Date',
            'Returned Date',
            'Receipt #',
            'Payment',
            'Fine Amount',
            'Condition',
            'Status',
            'Processed By',
            'Action',
        ];
    }

    public function map($loan): array
    {
        $isStudent = strtolower($loan->patron?->patronType?->name ?? '') === 'student';

        $borrowerName = trim(implode(' ', array_filter([
            ucwords($loan->patron?->first_name ?? ''),
            ucwords($loan->patron?->middle_name ?? ''),
            ucwords($loan->patron?->last_name ?? ''),
        ]))) . ($loan->patron?->suffix ? ' ' . strtoupper($loan->patron->suffix) : '');

        $processedBy = method_exists($loan->user, 'getFullNameAttribute')
            ? $loan->user?->getFullNameAttribute()
            : ($loan->user?->name ?? 'System User');

        // Payment column calculation
        $paymentStatus = '-';
        if ($loan->returned_at && $loan->fine_amount > 0) {
            $paymentStatus = $loan->is_paid ? 'Paid' : 'Unpaid';
        }

        return [
            strtoupper($loan->accession?->accession_number ?? 'N/A'),
            $loan->accession?->catalog?->title ? ucwords($loan->accession->catalog->title) : 'N/A',
            (string) ($loan->patron?->school_id ?? 'N/A'), // <--- Cast to string here
            $borrowerName ?: 'N/A',
            ucwords($loan->patron?->patronType?->name ?? 'N/A'),
            $loan->borrowed_at?->format('M d, Y h:i A') ?? '-',
            $isStudent ? ($loan->due_at?->format('M d, Y') ?? '-') : '-',
            $loan->returned_at?->format('M d, Y h:i A') ?? '-',
            $loan->receipt_number ?? '-',
            $paymentStatus,
            (float) ($loan->fine_amount ?? 0),
            ucfirst($loan->condition ?? 'Good'),
            ucfirst($loan->status ?? 'N/A'),
            $processedBy,
            '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'K' => '"₱"#,##0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $generatedBy = method_exists(auth()->user(), 'getFullNameAttribute')
                    ? auth()->user()->getFullNameAttribute() : 'System User';
                $roleName = auth()->user()?->role?->name ?? 'librarian';
                $searchLabel = $this->search ? $this->search : 'None (All applied)';
                $statusLabel = ucfirst($this->filterStatus ?? 'All Statuses');

                // Row 1: Main Title
                $sheet->setCellValue('A1', 'BPS LIBRARY MANAGEMENT SYSTEM');

                // Row 2: Report Subtitle
                $sheet->setCellValue('A2', 'Circulation Report');

                // Row 3: Generated On / Generated By
                $sheet->setCellValue('A3', 'Generated On: ' . now()->format('F d, Y h:i A'));
                $sheet->setCellValue('F3', 'Generated By: ' . $generatedBy);

                // Row 4: Search Filter / Role
                $sheet->setCellValue('A4', 'Search Filter: ' . $searchLabel);
                $sheet->setCellValue('F4', 'Role: ' . ucwords($roleName));
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
                    'color' => ['rgb' => '10257F'],
                ],
            ],

            // Subtitle Styling (Row 2)
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['rgb' => '333333'],
                ],
            ],

            // Meta Details Styling (Rows 3 & 4)
            3 => [
                'font' => [
                    'italic' => true,
                    'size' => 9,
                    'color' => ['rgb' => '555555'],
                ],
            ],
            4 => [
                'font' => [
                    'italic' => true,
                    'size' => 9,
                    'color' => ['rgb' => '555555'],
                ],
            ],

            // Table Header Styling (Row 5 - Dark Navy Accent)
            5 => [
                'font' => [
                    'bold' => true,
                    'size' => 9,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10257F'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
