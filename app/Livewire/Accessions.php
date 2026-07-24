<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\Catalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Accessions extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $accessionIdBeingEdited = null;
    public ?int $accessionIdBeingDeleted = null;

    // Form fields
    public ?int $acquisition_id = null;
    public ?int $catalog_id = null;
    public string $accession_number = '';
    public string $batch_number = '';
    public int $batch_qty = 1;
    public string $call_number = '';
    public string $condition = 'New';
    public string $status = 'Available';
    public string $acquired_date = '';
    public string $remarks = '';

    protected function rules(): array
    {
        $rules = [
            'acquisition_id' => 'required|exists:acquisitions,id',
            'catalog_id'     => 'required|exists:catalogs,id',
            'batch_number'   => 'required|string|max:50',
            'call_number'    => 'required|string|max:50',
            'condition'      => 'required|string|in:New,Good,Fair,Damaged',
            'status'         => 'required|string|in:Available,On Loan,Reserved,Under Maintenance,Lost,Withdrawn',
            'acquired_date'  => 'required|date',
            'remarks'        => 'nullable|string|max:1000',
        ];

        if ($this->accessionIdBeingEdited) {
            $rules['accession_number'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('accessions', 'accession_number')->ignore($this->accessionIdBeingEdited),
            ];
        } else {
            $remainingQty = $this->getRemainingQty();

            // Set min and max strictly to 0 if no items remain to prevent batch insertions
            $minAllowed = $remainingQty > 0 ? 1 : 0;
            $maxAllowed = $remainingQty;

            $rules['batch_qty'] = "required|integer|min:{$minAllowed}|max:{$maxAllowed}";
        }

        return $rules;
    }

    protected function messages(): array
    {
        $remaining = $this->getRemainingQty();

        return [
            'batch_qty.min' => $remaining === 0
                ? 'All assets for this acquisition have already been accessioned.'
                : 'Batch quantity must be at least 1.',
            'batch_qty.max' => $remaining === 0
                ? 'All assets for this acquisition have already been accessioned.'
                : "The batch quantity cannot exceed the remaining unaccessioned count ({$remaining}).",
        ];
    }

    public function getRemainingQty(): int
    {
        if (! $this->acquisition_id) {
            return 500;
        }

        $acquisition = Acquisition::find($this->acquisition_id);
        if (! $acquisition) {
            return 500;
        }

        $existingCount = Accession::where('acquisition_id', $this->acquisition_id)->count();

        return max(0, $acquisition->quantity - $existingCount);
    }

    #[Computed]
    public function selectedAcquisition(): ?Acquisition
    {
        if (! $this->acquisition_id) {
            return null;
        }

        return Acquisition::with([
            'catalog.author',
            'catalog.assetType',
            'catalog.publisher',
            'vendor'
        ])->find($this->acquisition_id);
    }

    public function updatedAcquisitionId($value): void
    {
        if ($value) {
            $acquisition = Acquisition::find($value);
            if ($acquisition) {
                $this->catalog_id = $acquisition->catalog_id;

                if (! $this->accessionIdBeingEdited) {
                    $remainingQty = $this->getRemainingQty();

                    $this->batch_qty = $remainingQty;
                    $this->batch_number = 'B-' . date('Ymd-Hi');
                    $this->accession_number = $this->generateAccessionNumber();
                    $this->acquired_date = $acquisition->received_date ? $acquisition->received_date->format('Y-m-d') : now()->format('Y-m-d');
                }
            }
        } else {
            $this->catalog_id = null;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'acquisition_id', 'catalog_id', 'accession_number', 'batch_number',
            'call_number', 'condition', 'status', 'acquired_date',
            'remarks', 'accessionIdBeingEdited',
        ]);

        $this->batch_qty = 1;
        $this->condition = 'New';
        $this->status = 'Available';
        $this->acquired_date = now()->format('Y-m-d');
        $this->batch_number = 'B-' . date('Ymd-Hi');
        $this->accession_number = $this->generateAccessionNumber();
        $this->showModal = true;
    }

    private function generateAccessionNumber(?int $offset = 0): string
    {
        $year = date('Y');
        $latest = Accession::whereYear('created_at', $year)->latest('id')->first();
        $baseNum = $latest ? ((int) substr($latest->accession_number, -5)) : 0;
        $nextNum = str_pad($baseNum + 1 + $offset, 5, '0', STR_PAD_LEFT);

        return "ACC-{$year}-{$nextNum}";
    }

    public function openEditModal(Accession $accession): void
    {
        $this->resetValidation();
        $this->accessionIdBeingEdited = $accession->id;
        $this->acquisition_id = $accession->acquisition_id;
        $this->catalog_id = $accession->catalog_id;
        $this->accession_number = $accession->accession_number;
        $this->batch_number = $accession->batch_number;
        $this->call_number = $accession->call_number;
        $this->condition = $accession->condition;
        $this->status = $accession->status;
        $this->acquired_date = $accession->acquired_date ? $accession->acquired_date->format('Y-m-d') : '';
        $this->remarks = $accession->remarks ?? '';
        $this->showModal = true;
    }

    public function saveAccession(): void
    {
        $this->validate();

        if ($this->accessionIdBeingEdited) {
            Accession::where('id', $this->accessionIdBeingEdited)->update([
                'acquisition_id'   => $this->acquisition_id,
                'catalog_id'       => $this->catalog_id,
                'accession_number' => trim($this->accession_number),
                'batch_number'     => trim($this->batch_number),
                'call_number'      => trim($this->call_number),
                'condition'        => $this->condition,
                'status'           => $this->status,
                'acquired_date'    => $this->acquired_date,
                'remarks'          => trim($this->remarks) ?: null,
            ]);

            $message = 'Accession record updated successfully.';
        } else {
            // Hard check backend remaining quantity
            $remainingQty = $this->getRemainingQty();
            if ($remainingQty <= 0) {
                $this->addError('batch_qty', 'All assets for this acquisition have already been accessioned.');
                return;
            }

            DB::transaction(function () {
                $records = [];
                $now = now();

                for ($i = 0; $i < $this->batch_qty; $i++) {
                    $records[] = [
                        'acquisition_id'   => $this->acquisition_id,
                        'catalog_id'       => $this->catalog_id,
                        'accession_number' => $this->generateAccessionNumber($i),
                        'batch_number'     => trim($this->batch_number),
                        'call_number'      => trim($this->call_number),
                        'condition'        => $this->condition,
                        'status'           => $this->status,
                        'acquired_date'    => $this->acquired_date,
                        'remarks'          => trim($this->remarks) ?: null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                Accession::insert($records);
            });

            $message = "Successfully created a batch of {$this->batch_qty} accession records.";
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->accessionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAccession(): void
    {
        if ($this->accessionIdBeingDeleted) {
            Accession::destroy($this->accessionIdBeingDeleted);
            $this->dispatch('toast', message: 'Accession item deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->accessionIdBeingDeleted = null;
    }

    #[Layout('components.layouts.app')]
    #[Title('Accessions')]
    public function render()
    {
        $accessions = Accession::with(['catalog.author', 'catalog.assetType', 'acquisition'])
            ->when($this->search, function ($query) {
                $query->where('accession_number', 'like', "%{$this->search}%")
                    ->orWhere('batch_number', 'like', "%{$this->search}%")
                    ->orWhere('call_number', 'like', "%{$this->search}%")
                    ->orWhereHas('catalog', fn ($q) => $q->where('title', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.accessions', [
            'accessions'   => $accessions,
            'acquisitions' => Acquisition::with('catalog')->latest()->get(),
        ]);
    }
}
