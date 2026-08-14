<?php

namespace App\Livewire;

use App\Models\Circulation;
use App\Models\Patron;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PatronRecords extends Component
{
    use WithPagination;

    // Search and Filters
    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'active', 'inactive'
    public ?int $selectedPatronId = null;

    // Transaction History Filters for Selected Patron
    public string $loanFilter = 'all'; // 'all', 'active', 'returned', 'overdue', 'lost'

    public function updatingSearch(): void
    {
        $this->resetPage('patronPage');
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage('patronPage');
    }

    public function selectPatron(int $id): void
    {
        $this->selectedPatronId = $id;
        $this->resetPage('loanPage');
    }

    public function clearSelectedPatron(): void
    {
        $this->selectedPatronId = null;
    }

    public function togglePatronStatus(int $id): void
    {
        $patron = Patron::findOrFail($id);
        $patron->status = $patron->status === 'active' ? 'inactive' : 'active';
        $patron->save();

        $this->dispatch('toast', message: "Patron status updated to {$patron->status}.", type: 'success');
    }

    #[Layout('components.layouts.app')]
    #[Title('Patron Records & History')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Fetch List of Patrons with Active & Total Loans Count
        $patrons = Patron::with(['patronType', 'gradeLevel', 'section'])
            ->withCount([
                'circulations as active_loans_count' => fn ($q) => $q->whereIn('status', ['borrowed', 'overdue']),
                'circulations as total_loans_count',
            ])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('patron_id', $likeOperator, "%{$this->search}%")
                        ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                        ->orWhere('email', $likeOperator, "%{$this->search}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(10, ['*'], 'patronPage');

        // Fetch Selected Patron Details & Their Circulation History
        $selectedPatron = $this->selectedPatronId
            ? Patron::with(['patronType', 'gradeLevel', 'section'])->find($this->selectedPatronId)
            : null;

        $patronLoans = $selectedPatron
            ? Circulation::with(['accession.catalog.author'])
                ->where('patron_id', $selectedPatron->id)
                ->when($this->loanFilter === 'active', fn ($q) => $q->whereIn('status', ['borrowed', 'overdue']))
                ->when($this->loanFilter === 'returned', fn ($q) => $q->where('status', 'returned'))
                ->when($this->loanFilter === 'overdue', fn ($q) => $q->where('status', 'overdue'))
                ->when($this->loanFilter === 'lost', fn ($q) => $q->where('status', 'lost'))
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
