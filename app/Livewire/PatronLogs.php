<?php

namespace App\Livewire;

use App\Exports\BorrowerLogsExport;
use App\Models\PatronLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatronLogs extends Component
{
    use WithPagination;

    // Search and Filters for Datatable
    public string $search = '';
    public ?string $filterDate = null; // Set to null by default to show all dates
    public string $filterStatus = 'all'; // 'all', 'inside', 'logged_out'

    // Mass Logout Modal State
    public bool $showForceCheckoutModal = false;

    public function mount(): void
    {
        // Keep null to show all records by default, or set to today if preferred:
        // $this->filterDate = now()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        if ($this->filterDate === '') {
            $this->filterDate = null;
        }
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all filters to view all historical records.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'filterDate', 'filterStatus']);
        $this->resetPage();
    }

    // ------------------------------------------------------------------
    // EXPORT ACTIONS
    // ------------------------------------------------------------------
    public function exportExcel(): BinaryFileResponse
    {
        $dateSuffix = $this->filterDate ? $this->filterDate : 'all-dates';
        $filename = 'borrower-attendance-logs-' . $dateSuffix . '-' . now()->format('His') . '.xlsx';

        return Excel::download(
            new BorrowerLogsExport($this->search, $this->filterDate ?? '', $this->filterStatus),
            $filename
        );
    }

    public function exportPdf(): StreamedResponse
    {
        $logs = $this->buildLogsQuery()->get();

        $pdf = Pdf::loadView('pdf.borrower-logs', [
            'logs'         => $logs,
            'filterDate'   => $this->filterDate ?? 'All Dates',
            'filterStatus' => $this->filterStatus,
        ])->setPaper('a4', 'landscape');

        $dateSuffix = $this->filterDate ? $this->filterDate : 'all-dates';
        $filename = 'borrower-attendance-logs-' . $dateSuffix . '-' . now()->format('His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    // ------------------------------------------------------------------
    // MANUAL & MASS ACTIONS
    // ------------------------------------------------------------------
    public function manualCheckOut(int $logId): void
    {
        $log = PatronLog::find($logId);

        if ($log && ! $log->time_out) {
            $log->update(['time_out' => now()]);
            $this->dispatch('toast', message: 'Borrower logged out manually.', type: 'success');
        }
    }

    /**
     * Mass check-out active patrons for the active filter date (or today).
     */
    public function checkoutAllActive(): void
    {
        $targetDate = ! empty($this->filterDate) ? $this->filterDate : now()->toDateString();

        $updatedCount = PatronLog::whereDate('log_date', $targetDate)
            ->whereNull('time_out')
            ->update(['time_out' => now()]);

        $this->showForceCheckoutModal = false;
        $this->resetPage();

        if ($updatedCount > 0) {
            $this->dispatch('toast', message: "Successfully logged out {$updatedCount} active borrower(s).", type: 'success');
        } else {
            $this->dispatch('toast', message: 'No active borrowers found to log out.', type: 'info');
        }
    }

    // ------------------------------------------------------------------
    // HELPER QUERY
    // ------------------------------------------------------------------
    private function buildLogsQuery()
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return PatronLog::with(['patron.patronType', 'patron.gradeLevel', 'patron.section'])
            // Only apply date filtering if $filterDate is not empty/null
            ->when(! empty($this->filterDate), fn ($q) => $q->whereDate('log_date', $this->filterDate))
            ->when($this->filterStatus === 'inside', fn ($q) => $q->whereNull('time_out'))
            ->when($this->filterStatus === 'logged_out', fn ($q) => $q->whereNotNull('time_out'))
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $likeOperator) {
                $query->whereHas('patron', function ($q) use ($searchTerm, $likeOperator) {
                    $q->where('rfid_tag', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('school_id', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('first_name', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('middle_name', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('last_name', $likeOperator, "%{$searchTerm}%");
                });
            })
            ->latest('id');
    }

    // ------------------------------------------------------------------
    // RENDER
    // ------------------------------------------------------------------
    #[Layout('components.layouts.app')]
    #[Title('Borrower Attendance Logs')]
    public function render(): View
    {
        // If a filter date is selected, stats evaluate for that date; otherwise, default to today for dashboard stats.
        $statsDate = ! empty($this->filterDate) ? $this->filterDate : now()->toDateString();

        return view('livewire.patron-logs', [
            'logs'            => $this->buildLogsQuery()->paginate(15),
            'totalToday'      => PatronLog::whereDate('log_date', $statsDate)->count(),
            'currentlyInside' => PatronLog::whereDate('log_date', $statsDate)->whereNull('time_out')->count(),
            'checkedOutToday' => PatronLog::whereDate('log_date', $statsDate)->whereNotNull('time_out')->count(),
        ]);
    }
}
