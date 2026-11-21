<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Circulation;
use App\Models\Patron;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PatronRecords extends Component
{
    use WithPagination, SanitizesInputs;

    // Search and Filters
    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'active', 'inactive'
    public ?int $selectedPatronId = null;

    // Transaction History Filters for Selected Patron
    public string $loanFilter = 'all'; // 'all', 'active', 'overdue', 'returned'

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage('patronPage');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage('patronPage');
    }

    public function updatedLoanFilter(): void
    {
        $this->resetPage('loanPage');
    }

    public function selectPatron(int $id): void
    {
        $this->selectedPatronId = $id;
        $this->resetPage('loanPage');
    }

    public function clearSelectedPatron(): void
    {
        $this->selectedPatronId = null;
        $this->resetPage('loanPage');
    }

    public function togglePatronStatus(int $id): void
    {
        $patron = Patron::findOrFail($id);
        $patron->status = $patron->status === 'active' ? 'inactive' : 'active';
        $patron->save();

        $this->dispatch('toast', message: "PATRON STATUS UPDATED TO {$patron->status}.", type: 'success');
    }

    #[Layout('components.layouts.app')]
    #[Title('Patron Records & History')]
    public function render()
    {
        $likeOperator = $this->likeOperator();

        $patrons = Patron::with(['patronType', 'gradeLevel', 'section'])
            ->withCount([
                'circulations as active_loans_count' => fn ($q) => $q->where('status', 'borrowed'),
                'circulations as total_loans_count',
            ])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) use ($likeOperator) {
                $term = "%{$this->search}%";
                $q->where(function ($sub) use ($likeOperator, $term) {
                    $sub->where('school_id', $likeOperator, $term)
                        ->orWhere('first_name', $likeOperator, $term)
                        ->orWhere('last_name', $likeOperator, $term)
                        ->orWhere('email', $likeOperator, $term);
                });
            })
            ->orderBy('last_name')
            ->paginate(10, ['*'], 'patronPage');

        $selectedPatron = $this->selectedPatronId
            ? Patron::with(['patronType', 'gradeLevel', 'section'])->find($this->selectedPatronId)
            : null;

        $patronLoans = $selectedPatron
            ? Circulation::with(['accession.catalog.author', 'accession.catalog.assetType'])
                ->where('patron_id', $selectedPatron->id)
                ->when($this->loanFilter === 'active', function ($q) {
                    $q->where('status', 'borrowed')
                        ->where(function ($sub) {
                            $sub->whereNull('due_at')
                                ->orWhere('due_at', '>=', now());
                        });
                })
                ->when($this->loanFilter === 'overdue', function ($q) {
                    $q->where('status', 'borrowed')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now());
                })
                ->when($this->loanFilter === 'returned', function ($q) {
                    $q->whereIn('status', ['returned', 'lost']);
                })
                ->latest('borrowed_at')
                ->paginate(10, ['*'], 'loanPage')
            : null;

        return view('livewire.patron-records', [
            'patrons'        => $patrons,
            'selectedPatron' => $selectedPatron,
            'patronLoans'    => $patronLoans,
        ]);
    }
}
