<?php

namespace App\Exports;

use App\Models\PatronLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class BorrowerLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected string $search;
    protected string $filterDate;
    protected string $filterStatus;

    public function __construct(string $search = '', string $filterDate = '', string $filterStatus = 'all')
    {
        $this->search = $search;
        $this->filterDate = $filterDate;
        $this->filterStatus = $filterStatus;
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return PatronLog::with(['patron.patronType', 'patron.gradeLevel', 'patron.section'])
            ->when($this->filterDate, fn ($q) => $q->whereDate('log_date', $this->filterDate))
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

    public function headings(): array
    {
        return [
            'Log Date',
            'School ID',
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
        $fullName = implode(' ', array_filter([
            $log->patron->first_name ?? '',
            $log->patron->middle_name ?? '',
            $log->patron->last_name ?? 'Deleted Borrower',
            $log->patron->suffix ?? '',
        ]));

        $gradeSection = ($log->patron && $log->patron->gradeLevel)
            ? (ucwords($log->patron->gradeLevel->name) . ' - ' . (ucwords($log->patron->section->name) ?? ''))
            : 'N/A';

        return [
            Carbon::parse($log->log_date)->format('M d, Y'),
            $log->patron->school_id ?? 'N/A',
            $fullName,
            $log->patron->patronType->name ?? 'N/A',
            $gradeSection,
            Carbon::parse($log->time_in)->format('h:i:s A'),
            $log->time_out ? Carbon::parse($log->time_out)->format('h:i:s A') : '--:--:--',
            $log->time_out ? 'Logged Out' : 'Inside Library',
        ];
    }
}
