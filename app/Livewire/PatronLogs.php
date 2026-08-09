<?php

namespace App\Livewire;

use App\Models\Patron;
use App\Models\PatronLog;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PatronLogs extends Component
{
    use WithPagination;

    // Active Tab ('scanner' or 'logs')
    public string $activeTab = 'scanner';

    // RFID Scan Input
    public string $rfidScan = '';

    // Search and Filters for Datatable
    public string $search = '';
    public string $filterDate = '';
    public string $filterStatus = 'all'; // 'all', 'inside', 'logged_out'

    // Recent Activity Feedback (Top Scan Banner)
    public ?array $lastScannedPatron = null;

    // Mass Logout Modal State
    public bool $showForceCheckoutModal = false;

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
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

    /**
     * Helper to format full name using middle_name & suffix
     */
    private function formatFullName(Patron $patron): string
    {
        return implode(' ', array_filter([
            $patron->first_name,
            $patron->middle_name,
            $patron->last_name,
            $patron->suffix,
        ]));
    }

    // ------------------------------------------------------------------
    // RFID SCAN HANDLER
    // ------------------------------------------------------------------
    public function processRfidScan(): void
    {
        $scannedCode = trim($this->rfidScan);
        $this->rfidScan = '';

        if (empty($scannedCode)) {
            return;
        }

        // Query patron matching patron_id directly
        $patron = Patron::with(['patronType', 'gradeLevel', 'section'])
            ->where('patron_id', $scannedCode)
            ->first();

        if (! $patron) {
            $this->lastScannedPatron = [
                'status'  => 'error',
                'message' => "Unregistered RFID / Patron ID: [{$scannedCode}]",
            ];
            $this->dispatch('play-sound', type: 'error');
            return;
        }

        if ($patron->status !== 'active') {
            $fullName = $this->formatFullName($patron);
            $this->lastScannedPatron = [
                'status'  => 'error',
                'message' => "Patron {$fullName} is currently marked as {$patron->status}.",
            ];
            $this->dispatch('play-sound', type: 'error');
            return;
        }

        $today = now()->toDateString();

        // Check if patron currently has an open session for today
        $activeLog = PatronLog::where('patron_id', $patron->id)
            ->whereNull('time_out')
            ->whereDate('log_date', $today)
            ->latest('time_in')
            ->first();

        $fullName = $this->formatFullName($patron);
        $totalVisitsToday = PatronLog::where('patron_id', $patron->id)
            ->whereDate('log_date', $today)
            ->count();

        $commonData = [
            'name'           => $fullName,
            'first_name'     => $patron->first_name,
            'last_name'      => $patron->last_name,
            'patron_id'      => $patron->patron_id,
            'grade'          => $patron->gradeLevel->name ?? 'N/A',
            'section'        => $patron->section->name ?? 'N/A',
            'address'        => $patron->address ?? 'N/A',
            'email'          => $patron->email ?? 'N/A',
            'contact_number' => $patron->contact_number ?? 'N/A',
            'type'           => $patron->patronType->name ?? 'N/A',
            'photo_url'      => $patron->photo_path ? asset('storage/' . $patron->photo_path) : null,
            'visits_today'   => $totalVisitsToday + ($activeLog ? 0 : 1),
        ];

        if ($activeLog) {
            // Log Out Process - Send full DateTime instance to timestamp column
            $now = now();
            $activeLog->update([
                'time_out' => $now,
            ]);

            $timeIn = Carbon::parse($activeLog->time_in);

            $this->lastScannedPatron = array_merge($commonData, [
                'status'   => 'out',
                'action'   => 'LOGGED OUT',
                'time_in'  => $timeIn->format('h:i:s A'),
                'time_out' => $now->format('h:i:s A'),
                'duration' => $timeIn->diffForHumans($now, true),
            ]);

            $this->dispatch('play-sound', type: 'out');
        } else {
            // Log In Process - Send full DateTime instance to timestamp column
            $now = now();
            PatronLog::create([
                'patron_id' => $patron->id,
                'time_in'   => $now,
                'log_date'  => $today,
            ]);

            $this->lastScannedPatron = array_merge($commonData, [
                'status'   => 'in',
                'action'   => 'LOGGED IN',
                'time_in'  => $now->format('h:i:s A'),
                'time_out' => null,
                'duration' => null,
            ]);

            $this->dispatch('play-sound', type: 'in');
        }
    }

    // ------------------------------------------------------------------
    // MANUAL ACTIONS
    // ------------------------------------------------------------------
    public function manualCheckOut(int $logId): void
    {
        $log = PatronLog::findOrFail($logId);

        if (! $log->time_out) {
            $log->update(['time_out' => now()]);
            $this->dispatch('toast', message: 'Patron logged out manually.', type: 'success');
        }
    }

    public function checkoutAllActive(): void
    {
        $updatedCount = PatronLog::whereDate('log_date', now()->toDateString())
            ->whereNull('time_out')
            ->update(['time_out' => now()]);

        $this->showForceCheckoutModal = false;
        $this->dispatch('toast', message: "Logged out {$updatedCount} active patrons.", type: 'success');
    }

    // ------------------------------------------------------------------
    // RENDER
    // ------------------------------------------------------------------
    #[Layout('components.layouts.app')]
    #[Title('Patron Attendance Logs')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $logs = PatronLog::with(['patron.patronType', 'patron.gradeLevel', 'patron.section'])
            ->when($this->filterDate, fn ($q) => $q->whereDate('log_date', $this->filterDate))
            ->when($this->filterStatus === 'inside', fn ($q) => $q->whereNull('time_out'))
            ->when($this->filterStatus === 'logged_out', fn ($q) => $q->whereNotNull('time_out'))
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->whereHas('patron', function ($q) use ($likeOperator) {
                    $q->where('patron_id', $likeOperator, "%{$this->search}%")
                        ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('last_name', $likeOperator, "%{$this->search}%");
                });
            })
            ->latest('id');

        $todayStr = now()->toDateString();

        return view('livewire.patron-logs', [
            'logs'            => $logs->paginate(15),
            'totalToday'      => PatronLog::whereDate('log_date', $todayStr)->count(),
            'currentlyInside' => PatronLog::whereDate('log_date', $todayStr)->whereNull('time_out')->count(),
            'checkedOutToday' => PatronLog::whereDate('log_date', $todayStr)->whereNotNull('time_out')->count(),
        ]);
    }
}
