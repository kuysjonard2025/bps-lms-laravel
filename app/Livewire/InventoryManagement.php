<?php

namespace App\Livewire;

use App\Models\Catalog;
use App\Models\AssetType;
use App\Models\Accession;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryManagement extends Component
{
    use WithPagination;

    // Search & Filters
    public string $search = '';
    public string $assetTypeFilter = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingAssetTypeFilter(): void { $this->resetPage(); }

    #[Layout('components.layouts.app')]
    #[Title('Inventory Summary')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Overall Aggregate Metrics for Header Cards
        $stats = [
            'total_items'     => Accession::count(),
            'total_available' => Accession::where('status', 'Available')->count(),
            'total_on_loan'   => Accession::where('status', 'On Loan')->count(),
            'total_issues'    => Accession::whereIn('condition', ['Damaged', 'Missing'])
                                   ->orWhereIn('status', ['Under Maintenance', 'Lost', 'Withdrawn'])
                                   ->count(),
        ];

        // Query Catalog with pre-aggregated stock counters
        $catalogs = Catalog::with(['author', 'assetType'])
            ->withCount([
                'accessions as total_copies',
                'accessions as available_copies' => fn ($q) => $q->where('status', 'Available'),
                'accessions as on_loan_copies'   => fn ($q) => $q->where('status', 'On Loan'),
                'accessions as reserved_copies'  => fn ($q) => $q->where('status', 'Reserved'),
                'accessions as maintenance_copies' => fn ($q) => $q->where('status', 'Under Maintenance'),
                'accessions as damaged_missing_copies' => fn ($q) => $q->whereIn('condition', ['Damaged', 'Missing'])
                                                                        ->orWhereIn('status', ['Lost', 'Withdrawn']),
                'accessions as acquisition_batches' => fn ($q) => $q->select(DB::raw('count(distinct(acquisition_id))')),
            ])
            ->when($this->assetTypeFilter !== 'all', fn ($q) => $q->where('asset_type_id', $this->assetTypeFilter))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('title', $likeOperator, "%{$this->search}%")
                        ->orWhere('isbn_issn', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->search}%"))
                        ->orWhereHas('accessions', fn ($acc) => $acc->where('call_number', $likeOperator, "%{$this->search}%"));
                });
            })
            ->latest()
            ->paginate(15);

        $assetTypes = AssetType::orderBy('name')->get();

        return view('livewire.inventory-management', [
            'catalogs'   => $catalogs,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
        ]);
    }
}
