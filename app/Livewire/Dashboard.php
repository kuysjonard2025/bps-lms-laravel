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
        $totalBooks = Accession::count();

        // Active borrows are items with no return date
        $activeBorrows = Circulation::whereNull('returned_at')->count();

        // Overdue books are active borrows past their due date
        $overdueBooks = Circulation::whereNull('returned_at')
            ->where('due_at', '<', now())
            ->count();

        $totalPatrons = Patron::count();

        // 2. Urgent Overdue Alerts
        $overdueAlerts = Circulation::with(['patron', 'accession.catalog'])
            ->whereNull('returned_at')
            ->where('due_at', '<', now())
            ->orderBy('due_at', 'asc')
            ->take(10)
            ->get();

        // 3. Recent Transactions List
        $recentTransactions = Circulation::with([
            'patron',
            'accession.catalog.author',
            'user'
        ])
        ->when($this->search, function ($q) use ($likeOperator) {
            $q->where(function ($sub) use ($likeOperator) {
                $sub->whereHas('patron', function ($p) use ($likeOperator) {
                    $p->where('first_name', $likeOperator, "%{$this->search}%")
                      ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                      ->orWhere('school_id', $likeOperator, "%{$this->search}%");
                })
                ->orWhereHas('accession', function ($a) use ($likeOperator) {
                    $a->where('accession_number', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('catalog', function ($c) use ($likeOperator) {
                          $c->where('title', $likeOperator, "%{$this->search}%")
                            ->orWhereHas('author', function ($auth) use ($likeOperator) {
                                $auth->where('name', $likeOperator, "%{$this->search}%");
                            });
                      });
                });
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
