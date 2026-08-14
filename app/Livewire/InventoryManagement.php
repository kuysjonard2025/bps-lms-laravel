<?php

namespace App\Livewire;

use App\Models\Catalog;
use App\Models\AssetType;
use App\Models\Accession;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryManagement extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $statusFilter = 'all';
    public string $conditionFilter = 'all';
    public string $assetTypeFilter = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingConditionFilter(): void { $this->resetPage(); }
    public function updatingAssetTypeFilter(): void { $this->resetPage(); }

    #[Layout('components.layouts.app')]
    #[Title('Inventory Management')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Stats Summary Across Entire Inventory
        $stats = [
            'total'           => Accession::count(),
            'available'       => Accession::where('status', 'Available')->count(),
            'on_loan'         => Accession::where('status', 'On Loan')->count(),
            'needs_attention' => Accession::whereIn('condition', ['Damaged', 'Missing'])
                                   ->orWhereIn('status', ['Under Maintenance', 'Lost', 'Withdrawn'])
                                   ->count(),
        ];

        // Catalogs Query grouped with Accession counters
        $catalogs = Catalog::with(['author', 'assetType', 'accessions' => function ($q) {
                $q->with('acquisition.vendor')
                  ->when($this->statusFilter !== 'all', fn ($sub) => $sub->where('status', $this->statusFilter))
                  ->when($this->conditionFilter !== 'all', fn ($sub) => $sub->where('condition', $this->conditionFilter));
            }])
            ->withCount([
                'accessions as total_copies',
                'accessions as available_copies' => fn ($q) => $q->where('status', 'Available'),
                'accessions as on_loan_copies' => fn ($q) => $q->where('status', 'On Loan'),
                'accessions as maintenance_copies' => fn ($q) => $q->whereIn('status', ['Under Maintenance', 'Lost', 'Withdrawn'])
                                                                    ->orWhereIn('condition', ['Damaged', 'Missing']),
            ])
            ->when($this->assetTypeFilter !== 'all', fn ($q) => $q->where('asset_type_id', $this->assetTypeFilter))
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->whereHas('accessions', fn ($acc) => $acc->where('status', $this->statusFilter));
            })
            ->when($this->conditionFilter !== 'all', function ($q) {
                $q->whereHas('accessions', fn ($acc) => $acc->where('condition', $this->conditionFilter));
            })
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('title', $likeOperator, "%{$this->search}%")
                        ->orWhere('isbn_issn', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->search}%"))
                        ->orWhereHas('accessions', function ($acc) use ($likeOperator) {
                            $acc->where('accession_number', $likeOperator, "%{$this->search}%")
                                ->orWhere('call_number', $likeOperator, "%{$this->search}%")
                                ->orWhere('batch_number', $likeOperator, "%{$this->search}%")
                                ->orWhereHas('acquisition', fn ($acq) => $acq->where('acquisition_number', $likeOperator, "%{$this->search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(10);

        $assetTypes = AssetType::orderBy('name')->get();

        return view('livewire.inventory-management', [
            'catalogs'   => $catalogs,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
        ]);
    }
}
