<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class UserActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = 'all';
    public string $moduleFilter = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }
    public function updatingModuleFilter(): void { $this->resetPage(); }

    #[Layout('components.layouts.app')]
    #[Title('User Activity Logs')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $stats = [
            'total_logs'    => ActivityLog::count(),
            'total_created' => ActivityLog::where('event', 'created')->count(),
            'total_updated' => ActivityLog::where('event', 'updated')->count(),
            'total_deleted' => ActivityLog::where('event', 'deleted')->count(),
        ];

        $modules = ActivityLog::select('log_name')->distinct()->pluck('log_name');

        $logs = ActivityLog::with('user')
            ->when($this->eventFilter !== 'all', fn ($q) => $q->where('event', $this->eventFilter))
            ->when($this->moduleFilter !== 'all', fn ($q) => $q->where('log_name', $this->moduleFilter))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('description', $likeOperator, "%{$this->search}%")
                        ->orWhere('ip_address', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', $likeOperator, "%{$this->search}%"));
                });
            })
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.user-activity-logs', [
            'logs'    => $logs,
            'stats'   => $stats,
            'modules' => $modules,
        ]);
    }
}
