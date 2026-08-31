<?php

namespace App\Livewire;

use App\Exports\BorrowerLogsExport;
use App\Models\PatronLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PatronLogs extends Component
{
    use WithPagination;

    // Search and Filters for Datatable
    public string $search = '';
    public string $filterDate = '';
    public string $filterStatus = 'all'; // 'all', 'inside', 'logged_out'

    // Mass Logout Modal State
    public bool $showForceCheckoutModal = false;

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    // ------------------------------------------------------------------
    // EXPORT ACTIONS
    // ------------------------------------------------------------------
    public function exportExcel()
    {
        $filename = 'borrower-attendance-logs-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new BorrowerLogsExport($this->search, $this->filterDate, $this->filterStatus),
            $filename
        );
    }

    public function exportPdf()
    {
        $logs = $this->buildLogsQuery()->get();

        $pdf = Pdf::loadView('pdf.borrower-logs', [
            'logs'         => $logs,
            'filterDate'   => $this->filterDate,
            'filterStatus' => $this->filterStatus,
        ])->setPaper('a4', 'landscape');

        $filename = 'borrower-attendance-logs-' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    // ------------------------------------------------------------------
    // MANUAL ACTIONS
    // ------------------------------------------------------------------
    public function manualCheckOut(int $logId): void
    {
        $log = PatronLog::findOrFail($logId);

        if (! $log->time_out) {
            $log->update(['time_out' => now()]);
            $this->dispatch('toast', message: 'Borrower logged out manually.', type: 'success');
        }
    }

    public function checkoutAllActive(): void
    {
        $updatedCount = PatronLog::whereDate('log_date', now()->toDateString())
            ->whereNull('time_out')
            ->update(['time_out' => now()]);

        $this->showForceCheckoutModal = false;
        $this->dispatch('toast', message: "Logged out {$updatedCount} active borrowers.", type: 'success');
    }

    // ------------------------------------------------------------------
    // HELPER QUERY
    // ------------------------------------------------------------------
    private function buildLogsQuery()
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return PatronLog::with(['patron.patronType', 'patron.gradeLevel', 'patron.section'])
            ->when($this->filterDate, fn ($q) => $q->whereDate('log_date', $this->filterDate))
            ->when($this->filterStatus === 'inside', fn ($q) => $q->whereNull('time_out'))
            ->when($this->filterStatus === 'logged_out', fn ($q) => $q->whereNotNull('time_out'))
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $likeOperator) {
                $query->whereHas('patron', function ($q) use ($searchTerm, $likeOperator) {
                    $q->where('rfid_tag', $likeOperator, "%{$searchTerm}%")
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
    public function render()
    {
        $todayStr = now()->toDateString();

        return view('livewire.patron-logs', [
            'logs'            => $this->buildLogsQuery()->paginate(15),
            'totalToday'      => PatronLog::whereDate('log_date', $todayStr)->count(),
            'currentlyInside' => PatronLog::whereDate('log_date', $todayStr)->whereNull('time_out')->count(),
            'checkedOutToday' => PatronLog::whereDate('log_date', $todayStr)->whereNotNull('time_out')->count(),
        ]);
    }
}
