<?php

namespace App\Exports;

use App\Models\PatronLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowerLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithStyles, WithEvents
{
    protected string $search;
    protected ?string $filterDate;
    protected string $filterStatus;
    protected string $generatedByName;
    protected string $generatedByRole;

    public function __construct(string $search = '', ?string $filterDate = null, string $filterStatus = 'all')
    {
        $this->search = trim(strip_tags($search));
        $this->filterDate = ! empty($filterDate) ? $filterDate : null;
        $this->filterStatus = $filterStatus;

        $user = auth()->user();
        $this->generatedByName = $user ? $user->getFullNameAttribute() : 'System Administrator';
        $this->generatedByRole = $user ? ($user->role ?? 'Staff') : 'Admin';
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return PatronLog::with(['patron.patronType', 'patron.gradeLevel', 'patron.section'])
            ->when(! empty($this->filterDate), fn ($q) => $q->whereDate('log_date', $this->filterDate))
            ->when($this->filterStatus === 'inside', fn ($q) => $q->whereNull('time_out'))
            ->when($this->filterStatus === 'logged_out', fn ($q) => $q->whereNotNull('time_out'))
            ->when($this->search !== '', function ($query) use ($likeOperator) {
                $query->whereHas('patron', function ($q) use ($likeOperator) {
                    $q->where('school_id', $likeOperator, "%{$this->search}%")
                        ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('last_name', $likeOperator, "%{$this->search}%");
                });
            })
            ->latest('id');
    }

    /**
     * Start data table from row 5 to match the target UI layout.
     */
    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'Log Date',
            'Student/Employee #',
            'Borrower Name',
            'Borrower Type',
            'Grade & Section',
            'Time In',
            'Time Out',
            'Status',
        ];
    }

    public function map($log): array
    {
        if ($log->patron) {
            $fullName = implode(' ', array_filter([
                $log->patron->first_name ?? '',
                $log->patron->middle_name ?? '',
                $log->patron->last_name ?? '',
                $log->patron->suffix ?? '',
            ]));
        } else {
            $fullName = 'Deleted Borrower';
        }

        $gradeSection = optional($log->patron)->gradeLevel
            ? (ucwords($log->patron->gradeLevel->name) . (optional($log->patron->section)->name ? ' - ' . ucwords($log->patron->section->name) : ''))
            : 'N/A';

        return [
            $log->log_date ? Carbon::parse($log->log_date)->format('M d, Y') : '',
            $log->patron->school_id ?? 'N/A',
            $fullName,
            $log->patron->patronType->name ?? 'N/A',
            $gradeSection,
            $log->time_in ? Carbon::parse($log->time_in)->format('h:i:s A') : '--:--:--',
            $log->time_out ? Carbon::parse($log->time_out)->format('h:i:s A') : '--:--:--',
            $log->time_out ? 'Logged Out' : 'Inside Library',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $systemName = config('app.name', 'BPS LIBRARY MANAGEMENT SYSTEM');
                $dateText = $this->filterDate ? Carbon::parse($this->filterDate)->format('M d, Y') : 'None (All applied)';
                $statusText = ucfirst(str_replace('_', ' ', $this->filterStatus));
                $searchText = $this->search !== '' ? '"' . $this->search . '"' : 'None (All applied)';

                // Set Metadata Rows matching the Acquisitions Report design
                $sheet->setCellValue('A1', strtoupper($systemName));
                $sheet->setCellValue('A2', 'Borrower Attendance Logs Report');

                $sheet->setCellValue('A3', 'Generated On: ' . now()->format('F d, Y h:i A'));
                $sheet->setCellValue('E3', 'Generated By: ' . $this->generatedByName);

                $sheet->setCellValue('A4', 'Search Filter: ' . $searchText);
                $sheet->setCellValue('E4', 'Role: ' . (ucwords($this->generatedByRole)));

                // Add a light background styling box or borders to the meta section if desired
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Title formatting
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => '1E3A8A']]],
            2 => ['font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => '475569']]],

            // Metadata info styling rows 3 and 4
            3 => ['font' => ['size' => 9, 'color' => ['argb' => '334155']]],
            4 => ['font' => ['size' => 9, 'color' => ['argb' => '334155']]],

            // Table Header Row (Row 5) styled with dark blue fill and white text matching the image theme
            5 => [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1E3A8A'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'CBD5E1'],
                    ],
                ],
            ],
        ];
    }
}
