<?php

namespace App\Livewire;

use App\Models\Acquisition;
use App\Models\Catalog;
use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Acquisitions extends Component
{
    use WithPagination;

    // Form Properties (Includes missing $unit_cost)
    public $acquisition_number;
    public $transaction_number;
    public $vendor_id = '';
    public $catalog_id = '';
    public $quantity = 1;
    public $unit_cost; // <--- Previously missing
    public $received_date;
    public $remarks;

    // Component Control Properties
    public $search = '';
    public $showModal = false;
    public $showDeleteModal = false;
    public $acquisitionIdBeingEdited = null;
    public $acquisitionIdBeingDeleted = null;

    protected function rules()
    {
        return [
            'transaction_number' => 'required|string|max:255',
            'vendor_id'          => 'required|exists:vendors,id',
            'catalog_id'         => 'required|exists:catalogs,id',
            'quantity'           => 'required|integer|min:1',
            'unit_cost'          => 'required|numeric|min:0',
            'received_date'      => 'required|date',
            'remarks'            => 'nullable|string|max:1000',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Dynamic Computations
    #[Computed]
    public function selectedVendor()
    {
        return $this->vendor_id ? Vendor::find($this->vendor_id) : null;
    }

    #[Computed]
    public function selectedCatalog()
    {
        return $this->catalog_id ? Catalog::with(['author', 'assetType', 'publisher'])->find($this->catalog_id) : null;
    }

    #[Computed]
    public function calculatedTotalCost()
    {
        $qty = (int) ($this->quantity ?? 0);
        $cost = (float) ($this->unit_cost ?? 0);

        return $qty * $cost;
    }

    // Modal Actions
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();

        $this->acquisition_number = 'ACQ-' . date('Y') . '-' . str_pad(Acquisition::count() + 1, 4, '0', STR_PAD_LEFT);
        $this->received_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $this->acquisitionIdBeingEdited = $id;

        $acq = Acquisition::findOrFail($id);

        $this->acquisition_number = $acq->acquisition_number;
        $this->transaction_number = $acq->transaction_number;
        $this->vendor_id          = $acq->vendor_id;
        $this->catalog_id         = $acq->catalog_id;
        $this->quantity           = $acq->quantity;
        $this->unit_cost          = $acq->unit_cost;
        $this->received_date      = $acq->received_date ? $acq->received_date->format('Y-m-d') : null;
        $this->remarks            = $acq->remarks;

        $this->showModal = true;
    }

    public function saveAcquisition()
    {
        $validated = $this->validate();

        if ($this->acquisitionIdBeingEdited) {
            $acq = Acquisition::findOrFail($this->acquisitionIdBeingEdited);
            $acq->update($validated);
        } else {
            $validated['acquisition_number'] = $this->acquisition_number;
            Acquisition::create($validated);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->acquisitionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAcquisition()
    {
        if ($this->acquisitionIdBeingDeleted) {
            Acquisition::findOrFail($this->acquisitionIdBeingDeleted)->delete();
        }

        $this->showDeleteModal = false;
        $this->acquisitionIdBeingDeleted = null;
    }

    private function resetForm()
    {
        $this->reset([
            'acquisition_number',
            'transaction_number',
            'vendor_id',
            'catalog_id',
            'quantity',
            'unit_cost',
            'received_date',
            'remarks',
            'acquisitionIdBeingEdited',
        ]);
        $this->quantity = 1;
    }

    #[Layout('components.layouts.app')]
    #[Title('Acquisitions')]
    public function render()
    {
        $acquisitions = Acquisition::with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($this->search, function ($query) {
                $query->where('acquisition_number', 'like', "%{$this->search}%")
                    ->orWhere('transaction_number', 'like', "%{$this->search}%")
                    ->orWhereHas('catalog', fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                    ->orWhereHas('vendor', fn($q) => $q->where('company_name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.acquisitions', [
            'acquisitions' => $acquisitions,
            'vendors'      => Vendor::orderBy('company_name')->get(),
            'catalogs'     => Catalog::orderBy('title')->get(),
        ]);
    }
}
