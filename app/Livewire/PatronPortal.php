<?php

namespace App\Livewire;

use App\Models\Catalog;
use App\Models\Circulation;
use App\Models\Patron;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PatronPortal extends Component
{
    use WithPagination;

    public string $activeTab = 'opac'; // 'opac' or 'transactions'

    // OPAC Filters
    public string $opacSearch = '';
    public string $opacAssetType = 'all';

    // Transaction Filters
    public string $transactionFilter = 'active'; // 'active' or 'history'

    public ?Patron $patron = null;

    public function mount(): void
    {
        $patronId = Session::get('patron_session_id');

        if (! $patronId) {
            $this->redirect(route('patron.login'), navigate: true);
            return;
        }

        $this->patron = Patron::with(['patronType', 'gradeLevel', 'section'])->find($patronId);

        if (! $this->patron) {
            Session::forget('patron_session_id');
            $this->redirect(route('patron.login'), navigate: true);
        }
    }

    public function logout(): void
    {
        Session::forget('patron_session_id');
        $this->redirect(route('patron.login'), navigate: true);
    }

    public function updatingOpacSearch(): void
    {
        $this->resetPage('opacPage');
    }

    #[Layout('components.layouts.guest')]
    #[Title('Patron Library Portal')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // 1. OPAC Catalog Query
        $catalogItems = Catalog::with(['author', 'assetType', 'accessions'])
            ->when($this->opacSearch, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('title', $likeOperator, "%{$this->opacSearch}%")
                        ->orWhere('isbn', $likeOperator, "%{$this->opacSearch}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->opacSearch}%"));
                });
            })
            ->latest()
            ->paginate(8, ['*'], 'opacPage');

        // 2. Patron Transactions Query
        $myLoans = Circulation::with(['accession.catalog.author'])
            ->where('patron_id', $this->patron?->id)
            ->when($this->transactionFilter === 'active', fn ($q) => $q->whereIn('status', ['borrowed', 'overdue']))
            ->when($this->transactionFilter === 'history', fn ($q) => $q->whereIn('status', ['returned', 'lost']))
            ->latest('borrowed_at')
            ->paginate(10, ['*'], 'loansPage');

        return view('livewire.patron-portal', [
            'catalogItems' => $catalogItems,
            'myLoans'      => $myLoans,
        ]);
    }
}
