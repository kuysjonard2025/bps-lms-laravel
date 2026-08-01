<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Acquisition;
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
    public bool $updateBatchCallNumber = false; // Toggle for batch updating call number
    public string $condition = 'New';
    public string $status = 'Available';
    public string $acquired_date = '';
    public string $remarks = '';

    protected function rules(): array
    {
        // Ensure catalog_id is always in sync with acquisition before validation
        if ($this->acquisition_id && ! $this->catalog_id) {
            $this->catalog_id = Acquisition::where('id', $this->acquisition_id)->value('catalog_id');
        }

        $rules = [
            'acquisition_id' => 'required|exists:acquisitions,id',
            'catalog_id'     => 'required|exists:catalogs,id',
            'batch_number'   => 'required|string|max:50',
            'call_number'    => 'required|string|max:50',
            'condition'      => 'required|string|in:New,Good,Fair,Damaged',
            // Fixed: Status defined as array so closures can be pushed cleanly
            'status'         => ['required', 'string', 'in:Available,On Loan,Reserved,Under Maintenance,Lost,Withdrawn'],
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

            // Prevent changing status manually if record is currently locked in an active state
            $rules['status'][] = function ($attribute, $value, $fail) {
                $accession = Accession::find($this->accessionIdBeingEdited);
                if ($accession && in_array($accession->status, ['On Loan', 'Reserved']) && $value !== $accession->status) {
                    $fail("Cannot change status directly while item state is '{$accession->status}'.");
                }
            };
        } else {
            $remainingQty = $this->getRemainingQty();
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
            return 0;
        }

        $acquisition = Acquisition::find($this->acquisition_id);
        if (! $acquisition) {
            return 0;
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
            'vendor',
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

                    $this->batch_qty = $remainingQty > 0 ? $remainingQty : 1;
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
            'remarks', 'accessionIdBeingEdited', 'updateBatchCallNumber',
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
        $latest = Accession::whereYear('created_at', $year)->orderByDesc('id')->first();

        $baseNum = 0;
        if ($latest && preg_match('/-(\d+)$/', $latest->accession_number, $matches)) {
            $baseNum = (int) $matches[1];
        }

        $nextNum = str_pad($baseNum + 1 + $offset, 5, '0', STR_PAD_LEFT);

        return "ACC-{$year}-{$nextNum}";
    }

    public function openEditModal(Accession $accession): void
    {
        if (in_array($accession->status, ['On Loan', 'Reserved'])) {
            $actionWord = $accession->status === 'On Loan' ? 'on loan' : 'reserved';
            $this->dispatch('toast', message: "Items currently {$actionWord} cannot be modified.", type: 'error');
            return;
        }

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
        $this->updateBatchCallNumber = false; // Default toggle off
        $this->showModal = true;
    }

    public function saveAccession(): void
    {
        $this->validate();

        if ($this->accessionIdBeingEdited) {
            $accession = Accession::findOrFail($this->accessionIdBeingEdited);

            if (in_array($accession->status, ['On Loan', 'Reserved'])) {
                $actionWord = $accession->status === 'On Loan' ? 'on loan' : 'reserved';
                $this->dispatch('toast', message: "Cannot edit an accession while it is currently {$actionWord}.", type: 'error');
                $this->showModal = false;
                return;
            }

            DB::transaction(function () use ($accession) {
                $callNumber = trim($this->call_number);

                // 1. Update individual item
                $accession->update([
                    'acquisition_id'   => $this->acquisition_id,
                    'catalog_id'       => $this->catalog_id,
                    'accession_number' => trim($this->accession_number),
                    'batch_number'     => trim($this->batch_number),
                    'call_number'      => $callNumber,
                    'condition'        => $this->condition,
                    'status'           => $this->status,
                    'acquired_date'    => $this->acquired_date,
                    'remarks'          => trim($this->remarks) ?: null,
                ]);

                // 2. Cascade call_number update to rest of the batch if toggled
                if ($this->updateBatchCallNumber && $this->batch_number) {
                    Accession::where('batch_number', $this->batch_number)
                        ->where('id', '!=', $accession->id)
                        ->whereNotIn('status', ['On Loan', 'Reserved'])
                        ->update(['call_number' => $callNumber]);
                }
            });

            $message = $this->updateBatchCallNumber
                ? "Accession record and related items in batch ({$this->batch_number}) updated successfully."
                : 'Accession record updated successfully.';
        } else {
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
        $accession = Accession::find($id);

        if ($accession && in_array($accession->status, ['On Loan', 'Reserved'])) {
            $actionWord = $accession->status === 'On Loan' ? 'on loan' : 'reserved';
            $this->dispatch('toast', message: "Cannot delete an item that is currently {$actionWord}.", type: 'error');
            return;
        }

        $this->accessionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAccession(): void
    {
        if ($this->accessionIdBeingDeleted) {
            $accession = Accession::find($this->accessionIdBeingDeleted);

            if ($accession) {
                if (in_array($accession->status, ['On Loan', 'Reserved'])) {
                    $actionWord = $accession->status === 'On Loan' ? 'checked out' : 'reserved';
                    $this->dispatch('toast', message: "Deletion blocked: Item is currently {$actionWord}.", type: 'error');
                    $this->showDeleteModal = false;
                    $this->accessionIdBeingDeleted = null;
                    return;
                }

                $accession->delete();
                $this->dispatch('toast', message: 'Accession item deleted successfully.', type: 'success');
            }
        }

        $this->showDeleteModal = false;
        $this->accessionIdBeingDeleted = null;
    }

    #[Layout('components.layouts.app')]
    #[Title('Accessions')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $accessions = Accession::with(['catalog.author', 'catalog.assetType', 'acquisition'])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('accession_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('batch_number', $likeOperator, "%{$this->search}%")
                      ->orWhere('call_number', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$this->search}%"));
                });
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
