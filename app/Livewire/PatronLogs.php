<?php // app/Livewire/PatronLogs.php

namespace App\Livewire;

use App\Exports\PatronLogsExport;
use App\Models\PatronLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PatronLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $filterDate = null;
    public string $filterStatus = 'all'; // 'all', 'inside', 'logged_out'

    public bool $showForceCheckoutModal = false;
    public bool $showManualCheckoutModal = false;
    public ?int $logIdBeingCheckedOut = null;

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        if (mb_strlen($this->search) > 100) {
            $this->search = mb_substr($this->search, 0, 100);
        }
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
        if (! in_array($this->filterStatus, ['all', 'inside', 'logged_out'], true)) {
            $this->filterStatus = 'all';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterDate', 'filterStatus']);
        $this->resetPage();
    }

    private function validateExportFilters(): void
    {
        $rules = [
            'search'       => ['nullable', 'string', 'max:100'],
            'filterDate'   => ['nullable', 'date'],
            'filterStatus' => ['required', 'string', 'in:all,inside,logged_out'],
        ];

        $messages = [
            'filterDate.date' => 'Please choose a valid filter date before exporting.',
            'filterStatus.in' => 'Selected attendance status filter is invalid.',
        ];

        $attributes = [
            'search'       => 'Search Keyword',
            'filterDate'   => 'Log Date Filter',
            'filterStatus' => 'Status Filter',
        ];

        $this->validate($rules, $messages, $attributes);
    }

    // ------------------------------------------------------------------
    // EXPORT ACTIONS
    // ------------------------------------------------------------------
    public function exportExcel(): ?BinaryFileResponse
    {
        $this->validateExportFilters();

        try {
            $dateSuffix = $this->filterDate ?: 'all-dates';
            $filename = 'patron-attendance-logs-' . $dateSuffix . '-' . now()->format('His') . '.xlsx';

            return Excel::download(
                new PatronLogsExport(trim($this->search), $this->filterDate ?? '', $this->filterStatus),
                $filename
            );
        } catch (Throwable $e) {
            Log::error('Excel export error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Failed To Export Excel File.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        $this->validateExportFilters();

        try {
            $logs = $this->buildLogsQuery()->get();

            $pdf = Pdf::loadView('pdf.patron-logs', [
                'logs'         => $logs,
                'filterDate'   => $this->filterDate ?? 'All Dates',
                'filterStatus' => $this->filterStatus,
            ])->setPaper('a4', 'landscape');

            $dateSuffix = $this->filterDate ?: 'all-dates';
            $filename = 'patron-attendance-logs-' . $dateSuffix . '-' . now()->format('His') . '.pdf';

            return response()->streamDownload(fn () => print($pdf->output()), $filename);
        } catch (Throwable $e) {
            Log::error('PDF export error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Failed To Export PDF File.', type: 'error');

            return null;
        }
    }

    // ------------------------------------------------------------------
    // MANUAL & MASS ACTIONS
    // ------------------------------------------------------------------
    public function openForceCheckoutModal(): void
    {
        $this->showForceCheckoutModal = true;
    }

    public function confirmManualCheckOut(int $logId): void
    {
        $this->logIdBeingCheckedOut = $logId;
        $this->showManualCheckoutModal = true;
    }

    public function manualCheckOut(): void
    {
        if (! $this->logIdBeingCheckedOut) {
            $this->showManualCheckoutModal = false;

            return;
        }

        try {
            $checkedOut = false;

            DB::transaction(function () use (&$checkedOut): void {
                $log = PatronLog::query()->lockForUpdate()->find($this->logIdBeingCheckedOut);

                if ($log && ! $log->time_out) {
                    $log->update(['time_out' => now()]);
                    $checkedOut = true;
                }
            });

            if ($checkedOut) {
                $this->dispatch('toast', message: 'Patron Logged Out Manually.', type: 'success');
            }
        } catch (Throwable $e) {
            Log::error('Manual checkout error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Error Occurred During Manual Check-Out.', type: 'error');
        } finally {
            $this->showManualCheckoutModal = false;
            $this->logIdBeingCheckedOut = null;
        }
    }

    public function checkoutAllActive(): void
    {
        try {
            $updatedCount = 0;

            DB::transaction(function () use (&$updatedCount): void {
                $updatedCount = PatronLog::query()
                    ->whereNull('time_out')
                    ->update(['time_out' => now()]);
            });

            $this->showForceCheckoutModal = false;
            $this->resetPage();

            if ($updatedCount > 0) {
                $this->dispatch('toast', message: "Successfully Logged Out {$updatedCount} Active Patron(s) System-Wide.", type: 'success');
            } else {
                $this->dispatch('toast', message: 'No Active Patrons Found To Log Out.', type: 'info');
            }
        } catch (Throwable $e) {
            Log::error('Mass checkout error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Unexpected System Error Occurred During Mass Check-Out.', type: 'error');
        }
    }

    // ------------------------------------------------------------------
    // HELPER QUERY
    // ------------------------------------------------------------------
    private function buildLogsQuery(): Builder
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = $this->likeOperator();

        return PatronLog::query()
            ->with(['patron.patronType', 'patron.department', 'patron.gradeLevel', 'patron.section'])
            ->when(! empty($this->filterDate), fn (Builder $q) => $q->whereDate('log_date', $this->filterDate))
            ->when($this->filterStatus === 'inside', fn (Builder $q) => $q->whereNull('time_out'))
            ->when($this->filterStatus === 'logged_out', fn (Builder $q) => $q->whereNotNull('time_out'))
            ->when($searchTerm !== '', function (Builder $query) use ($searchTerm, $likeOperator): void {
                $query->whereHas('patron', function (Builder $q) use ($searchTerm, $likeOperator): void {
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
    #[Title('Patron Attendance Logs')]
    public function render(): View
    {
        $statsDate = ! empty($this->filterDate) ? $this->filterDate : now()->toDateString();
        $totalToday = 0;
        $currentlyInside = 0;
        $checkedOutToday = 0;
        $logs = new LengthAwarePaginator([], 0, 15);

        try {
            $logs = $this->buildLogsQuery()->paginate(15);
            $totalToday = PatronLog::query()->whereDate('log_date', $statsDate)->count();
            $currentlyInside = PatronLog::query()->whereDate('log_date', $statsDate)->whereNull('time_out')->count();
            $checkedOutToday = PatronLog::query()->whereDate('log_date', $statsDate)->whereNotNull('time_out')->count();
        } catch (Throwable $e) {
            Log::error('Error rendering patron attendance logs: ' . $e->getMessage());
        }

        return view('livewire.patron-logs', [
            'logs'            => $logs,
            'totalToday'      => $totalToday,
            'currentlyInside' => $currentlyInside,
            'checkedOutToday' => $checkedOutToday,
        ]);
    }
}
