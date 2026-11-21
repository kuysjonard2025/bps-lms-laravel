<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Circulation;
use App\Models\Patron;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    #[Title('Dashboard')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $now = now();

        $totalAccessioned = 0;
        $unaccessionedCount = 0;
        $activeBorrows = 0;
        $overdueBooks = 0;
        $totalPatrons = 0;
        /** @var Collection<int, Circulation> $overdueAlerts */
        $overdueAlerts = collect();
        /** @var LengthAwarePaginator $recentTransactions */
        $recentTransactions = new Paginator([], 0, 10);

        try {
            $totalAccessioned = Accession::count();
            $unaccessionedCount = Accession::where(function ($q) {
                $q->whereNull('accession_number')
                    ->orWhere('accession_number', '');
            })->count();

            $activeBorrows = Circulation::whereNull('returned_at')->count();

            $overdueBooks = Circulation::whereNull('returned_at')
                ->where('due_at', '<', $now)
                ->count();

            $totalPatrons = Patron::count();

            $overdueAlerts = Circulation::with(['patron', 'accession.catalog'])
                ->whereNull('returned_at')
                ->where('due_at', '<', $now)
                ->orderBy('due_at', 'asc')
                ->take(10)
                ->get();

            $searchTerm = trim($this->search);

            $recentTransactions = Circulation::with([
                'patron.patronType',
                'accession.catalog.author',
                'user',
            ])
                ->when($searchTerm !== '', function ($q) use ($likeOperator, $searchTerm) {
                    $q->where(function ($sub) use ($likeOperator, $searchTerm) {
                        $sub->whereHas('patron', function ($p) use ($likeOperator, $searchTerm) {
                            $p->where('first_name', $likeOperator, "%{$searchTerm}%")
                                ->orWhere('last_name', $likeOperator, "%{$searchTerm}%")
                                ->orWhere('school_id', $likeOperator, "%{$searchTerm}%");
                        })
                            ->orWhereHas('accession', function ($a) use ($likeOperator, $searchTerm) {
                                $a->where('accession_number', $likeOperator, "%{$searchTerm}%")
                                    ->orWhereHas('catalog', function ($c) use ($likeOperator, $searchTerm) {
                                        $c->where('title', $likeOperator, "%{$searchTerm}%")
                                            ->orWhereHas('author', function ($auth) use ($likeOperator, $searchTerm) {
                                                $auth->where('name', $likeOperator, "%{$searchTerm}%");
                                            });
                                    });
                            });
                    });
                })
                ->when($this->statusFilter !== 'all', function ($q) use ($now) {
                    if ($this->statusFilter === 'returned') {
                        $q->whereNotNull('returned_at');
                    } elseif ($this->statusFilter === 'overdue') {
                        $q->whereNull('returned_at')
                            ->where('due_at', '<', $now);
                    } elseif ($this->statusFilter === 'borrowed') {
                        $q->whereNull('returned_at')
                            ->where(function ($sub) use ($now) {
                                $sub->whereNull('due_at')
                                    ->orWhere('due_at', '>=', $now);
                            });
                    }
                })
                ->latest()
                ->paginate(10);
        } catch (Throwable $e) {
            Log::error('Dashboard render error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->dispatch(
                'toast',
                type: 'error',
                message: 'Unable to load latest dashboard records. Please try again.'
            );
        }

        return view('livewire.dashboard', [
            'totalAccessioned'   => $totalAccessioned,
            'unaccessionedCount' => $unaccessionedCount,
            'activeBorrows'      => $activeBorrows,
            'overdueBooks'       => $overdueBooks,
            'totalPatrons'       => $totalPatrons,
            'overdueAlerts'      => $overdueAlerts,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
