<?php

namespace App\Livewire;

use App\Models\AuthLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class AuthenticationLogs extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $eventFilter = 'all';
    public string $dateRange = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }
    public function updatingDateRange(): void { $this->resetPage(); }

    #[Layout('components.layouts.app')]
    #[Title('Authentication Logs')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Overall Aggregate Metrics
        $stats = [
            'total_logs'    => AuthLog::count(),
            'login_success' => AuthLog::where('event', 'login_success')->count(),
            'login_failed'  => AuthLog::where('event', 'login_failed')->count(),
            'lockouts'      => AuthLog::where('event', 'lockout')->count(),
        ];

        // Query Logs with Filters
        $logs = AuthLog::with('user')
            ->when($this->eventFilter !== 'all', fn ($q) => $q->where('event', $this->eventFilter))
            ->when($this->dateRange !== 'all', function ($q) {
                match ($this->dateRange) {
                    'today'      => $q->whereDate('logged_at', today()),
                    '7_days'     => $q->where('logged_at', '>=', now()->subDays(7)),
                    '30_days'    => $q->where('logged_at', '>=', now()->subDays(30)),
                    default      => null,
                };
            })
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('email', $likeOperator, "%{$this->search}%")
                        ->orWhere('ip_address', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', $likeOperator, "%{$this->search}%"));
                });
            })
            ->orderBy('logged_at', 'desc')
            ->paginate(15);

        return view('livewire.authentication-logs', [
            'logs'  => $logs,
            'stats' => $stats,
        ]);
    }
}
