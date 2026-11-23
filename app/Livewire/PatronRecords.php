<?php // app/Livewire/PatronRecords.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Circulation;
use App\Models\Patron;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

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
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        if (mb_strlen($this->search) > 100) {
            $this->search = mb_substr($this->search, 0, 100);
        }
        $this->resetPage('patronPage');
    }

    public function updatedStatusFilter(): void
    {
        if (! in_array($this->statusFilter, ['all', 'active', 'inactive'], true)) {
            $this->statusFilter = 'all';
        }
        $this->resetPage('patronPage');
    }

    public function updatedLoanFilter(): void
    {
        if (! in_array($this->loanFilter, ['all', 'active', 'overdue', 'returned'], true)) {
            $this->loanFilter = 'all';
        }
        $this->resetPage('loanPage');
    }

    public function selectPatron(int $id): void
    {
        $this->selectedPatronId = $id;
        $this->loanFilter = 'all';
        $this->resetPage('loanPage');
    }

    public function clearSelectedPatron(): void
    {
        $this->selectedPatronId = null;
        $this->loanFilter = 'all';
        $this->resetPage('loanPage');
    }

    public function togglePatronStatus(int $id): void
    {
        try {
            $updatedStatus = null;

            DB::transaction(function () use ($id, &$updatedStatus): void {
                $patron = Patron::query()->lockForUpdate()->find($id);

                if (! $patron) {
                    return;
                }

                $patron->status = $patron->status === 'active' ? 'inactive' : 'active';
                $patron->save();
                $updatedStatus = strtoupper((string) $patron->status);
            });

            if ($updatedStatus) {
                $this->dispatch('toast', message: "PATRON STATUS UPDATED TO {$updatedStatus}.", type: 'success');
            } else {
                $this->dispatch('toast', message: 'PATRON RECORD NOT FOUND.', type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Error toggling patron status: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO UPDATE PATRON STATUS RIGHT NOW.', type: 'error');
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Patron Records & History')]
    public function render()
    {
        $likeOperator = $this->likeOperator();
        $term = '%' . addcslashes(trim($this->search), '%_\\') . '%';

        $patrons = new LengthAwarePaginator([], 0, 10, 1, ['pageName' => 'patronPage']);
        $selectedPatron = null;
        $patronLoans = null;

        try {
            $patrons = Patron::query()
                ->with(['patronType', 'department', 'gradeLevel', 'section'])
                ->withCount([
                    'circulations as active_loans_count' => fn (Builder $q) => $q->where('status', 'borrowed'),
                    'circulations as total_loans_count',
                ])
                ->when($this->statusFilter !== 'all', fn (Builder $q) => $q->where('status', $this->statusFilter))
                ->when(trim($this->search) !== '', function (Builder $q) use ($likeOperator, $term): void {
                    $q->where(function (Builder $sub) use ($likeOperator, $term): void {
                        $sub->where('school_id', $likeOperator, $term)
                            ->orWhere('first_name', $likeOperator, $term)
                            ->orWhere('middle_name', $likeOperator, $term)
                            ->orWhere('last_name', $likeOperator, $term)
                            ->orWhere('email', $likeOperator, $term);
                    });
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(10, ['*'], 'patronPage');

            if ($this->selectedPatronId) {
                $selectedPatron = Patron::query()
                    ->with(['patronType', 'department', 'gradeLevel', 'section'])
                    ->find($this->selectedPatronId);

                if (! $selectedPatron) {
                    $this->selectedPatronId = null;
                } else {
                    $patronLoans = Circulation::query()
                        ->with(['accession.catalog.author', 'accession.catalog.assetType'])
                        ->where('patron_id', $selectedPatron->id)
                        ->when($this->loanFilter === 'active', function (Builder $q): void {
                            $q->where('status', 'borrowed')
                                ->where(function (Builder $sub): void {
                                    $sub->whereNull('due_at')
                                        ->orWhere('due_at', '>=', now());
                                });
                        })
                        ->when($this->loanFilter === 'overdue', function (Builder $q): void {
                            $q->where('status', 'borrowed')
                                ->whereNotNull('due_at')
                                ->where('due_at', '<', now());
                        })
                        ->when($this->loanFilter === 'returned', function (Builder $q): void {
                            $q->whereIn('status', ['returned', 'lost']);
                        })
                        ->latest('borrowed_at')
                        ->paginate(10, ['*'], 'loanPage');
                }
            }
        } catch (Throwable $e) {
            Log::error('Error rendering patron records: ' . $e->getMessage());
        }

        return view('livewire.patron-records', [
            'patrons'        => $patrons,
            'selectedPatron' => $selectedPatron,
            'patronLoans'    => $patronLoans,
        ]);
    }
}
