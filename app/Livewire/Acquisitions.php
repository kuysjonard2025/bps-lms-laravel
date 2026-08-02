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

    // Form Properties
    public ?string $acquisition_number = null;
    public string $transaction_number = '';
    public ?int $vendor_id = null;
    public ?int $catalog_id = null;
    public int $quantity = 1;
    public $unit_cost = null;
    public ?string $received_date = null;
    public ?string $remarks = null;

    // Component Control Properties
    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $acquisitionIdBeingEdited = null;
    public ?int $acquisitionIdBeingDeleted = null;

    protected function rules(): array
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

    public function updatedVendorId($value): void
    {
        if ($value === '') {
            $this->vendor_id = null;
        }
    }

    public function updatedCatalogId($value): void
    {
        if ($value === '') {
            $this->catalog_id = null;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // Dynamic Computations
    #[Computed]
    public function selectedVendor(): ?Vendor
    {
        return $this->vendor_id ? Vendor::find($this->vendor_id) : null;
    }

    #[Computed]
    public function selectedCatalog(): ?Catalog
    {
        return $this->catalog_id ? Catalog::with(['author', 'assetType', 'publisher'])->find($this->catalog_id) : null;
    }

    #[Computed]
    public function calculatedTotalCost(): float
    {
        $qty = (int) ($this->quantity ?? 0);
        $cost = (float) ($this->unit_cost ?? 0);

        return $qty * $cost;
    }

    // Modal Actions
    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();

        $this->acquisition_number = $this->generateAcquisitionNumber();
        $this->received_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    private function generateAcquisitionNumber(): string
    {
        $year = date('Y');
        $latest = Acquisition::whereYear('created_at', $year)->orderByDesc('id')->first();

        $baseNum = 0;
        if ($latest && preg_match('/-(\d+)$/', $latest->acquisition_number, $matches)) {
            $baseNum = (int) $matches[1];
        }

        $nextNum = str_pad($baseNum + 1, 4, '0', STR_PAD_LEFT);

        return "ACQ-{$year}-{$nextNum}";
    }

    public function openEditModal(int $id): void
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

    public function saveAcquisition(): void
    {
        $validated = $this->validate();

        // Check for duplicate composite key (transaction_number + catalog_id + vendor_id)
        $exists = Acquisition::where('transaction_number', $this->transaction_number)
            ->where('catalog_id', $this->catalog_id)
            ->where('vendor_id', $this->vendor_id)
            ->when($this->acquisitionIdBeingEdited, fn ($query) => $query->where('id', '!=', $this->acquisitionIdBeingEdited))
            ->exists();

        if ($exists) {
            $this->addError('transaction_number', 'An acquisition record with this Transaction Number, Vendor, and Catalog item already exists.');
            return;
        }

        if ($this->acquisitionIdBeingEdited) {
            $acq = Acquisition::findOrFail($this->acquisitionIdBeingEdited);
            $acq->update($validated);
            $message = 'Acquisition record updated successfully.';
        } else {
            $validated['acquisition_number'] = $this->acquisition_number;
            Acquisition::create($validated);
            $message = 'Acquisition record created successfully.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->acquisitionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAcquisition(): void
    {
        if ($this->acquisitionIdBeingDeleted) {
            Acquisition::findOrFail($this->acquisitionIdBeingDeleted)->delete();
            $this->dispatch('toast', message: 'Acquisition record deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->acquisitionIdBeingDeleted = null;
    }

    private function resetForm(): void
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
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $acquisitions = Acquisition::with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('acquisition_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('transaction_number', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$this->search}%"))
                      ->orWhereHas('vendor', fn ($sub) => $sub->where('company_name', $likeOperator, "%{$this->search}%"));
                });
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
