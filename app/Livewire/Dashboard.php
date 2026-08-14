<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Circulation;
use App\Models\Patron;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    #[Title('Dashboard')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // 1. Key Metrics
        $totalBooks    = Accession::count();
        $activeBorrows = Circulation::where('status', 'borrowed')->count();
        $overdueBooks  = Circulation::where(function ($q) {
                            $q->where('status', 'overdue')
                              ->orWhere(function ($sub) {
                                  $sub->where('status', 'borrowed')
                                      ->where('due_at', '<', now());
                              });
                        })->count();
        $totalPatrons  = Patron::count();

        // 2. Overdue Alerts (Top urgent items)
        $overdueAlerts = Circulation::with(['patron', 'accession.catalog'])
            ->where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'borrowed')
                          ->where('due_at', '<', now());
                  });
            })
            ->orderBy('due_at', 'asc')
            ->take(10)
            ->get();

        // 3. Recent Transactions (Paginated)
        $recentTransactions = Circulation::with(['patron', 'accession.catalog', 'processedBy'])
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->whereHas('patron', function ($p) use ($likeOperator) {
                        $p->where('first_name', $likeOperator, "%{$this->search}%")
                          ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                          ->orWhere('card_number', $likeOperator, "%{$this->search}%");
                    })
                    ->orWhereHas('accession.catalog', function ($c) use ($likeOperator) {
                        $c->where('title', $likeOperator, "%{$this->search}%");
                    })
                    ->orWhere('transaction_number', $likeOperator, "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard', [
            'totalBooks'         => $totalBooks,
            'activeBorrows'      => $activeBorrows,
            'overdueBooks'       => $overdueBooks,
            'totalPatrons'       => $totalPatrons,
            'overdueAlerts'      => $overdueAlerts,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
