<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\AssetType;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingConditionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAssetTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updateItemStatus(int $id, string $status): void
    {
        $accession = Accession::findOrFail($id);
        $accession->status = $status;
        $accession->save();

        $this->dispatch('toast', message: "Accession #{$accession->accession_number} status updated to {$status}.", type: 'success');
    }

    public function updateItemCondition(int $id, string $condition): void
    {
        $accession = Accession::findOrFail($id);
        $accession->condition = $condition;
        $accession->save();

        $this->dispatch('toast', message: "Accession #{$accession->accession_number} condition updated to {$condition}.", type: 'success');
    }

    #[Layout('components.layouts.app')]
    #[Title('Inventory Management')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Stats Summary
        $stats = [
            'total'           => Accession::count(),
            'available'       => Accession::where('status', 'Available')->count(),
            'on_loan'         => Accession::where('status', 'On Loan')->count(),
            'needs_attention' => Accession::whereIn('condition', ['Damaged', 'Missing'])
                                   ->orWhereIn('status', ['Under Maintenance', 'Lost', 'Withdrawn'])
                                   ->count(),
        ];

        // Accessions Query aligned with exact schema
        $accessions = Accession::with(['catalog.assetType', 'catalog.author', 'acquisition.vendor'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->conditionFilter !== 'all', fn ($q) => $q->where('condition', $this->conditionFilter))
            ->when($this->assetTypeFilter !== 'all', function ($q) {
                $q->whereHas('catalog', fn ($cat) => $cat->where('asset_type_id', $this->assetTypeFilter));
            })
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('accession_number', $likeOperator, "%{$this->search}%")
                        ->orWhere('batch_number', $likeOperator, "%{$this->search}%")
                        ->orWhere('call_number', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('catalog', function ($c) use ($likeOperator) {
                            $c->where('title', $likeOperator, "%{$this->search}%")
                              ->orWhere('isbn_issn', $likeOperator, "%{$this->search}%")
                              ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(15);

        // Fetch Asset Types for filtering dropdown
        $assetTypes = AssetType::orderBy('name')->get();

        return view('livewire.inventory-management', [
            'accessions' => $accessions,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
        ]);
    }
}
