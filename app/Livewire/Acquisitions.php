<?php // app/Livewire/Acquisitions.php

namespace App\Livewire;

use App\Exports\AcquisitionsExcelExport;
use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\Catalog;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Acquisitions extends Component
{
    use WithPagination, SanitizesInputs;

    public ?string $acquisition_number = null;
    public string $transaction_number = '';
    public string $status = 'pending';
    public string $delivery_type = 'purchase';
    public ?int $vendor_id = null;
    public ?int $catalog_id = null;
    public int $quantity_ordered = 1;
    public int $quantity_received = 0;
    public ?float $unit_cost = null;
    public ?string $delivery_date = null;
    public ?string $received_date = null;
    public ?string $remarks = null;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showAccessionsModal = false;
    public ?Acquisition $viewingAcquisition = null;
    public ?int $acquisitionIdBeingEdited = null;
    public ?int $acquisitionIdBeingDeleted = null;

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function validationRules(): array
    {
        return [
            'transaction_number' => ['required', 'string', 'min:1', 'max:50'],
            'delivery_type'      => ['required', 'string', 'in:purchase,donation'],
            'status'             => ['required', 'string', 'in:pending,partial,delivered'],
            'vendor_id'          => ['required', 'integer', 'exists:vendors,id'],
            'catalog_id'         => ['required', 'integer', 'exists:catalogs,id'],
            'quantity_ordered'   => [
                'required',
                'integer',
                'min:1',
                'max:1000',
                function ($attribute, $value, $fail) {
                    if ($this->acquisitionIdBeingEdited) {
                        $accessionedCount = Accession::where('acquisition_id', $this->acquisitionIdBeingEdited)->count();

                        if ((int) $value < $accessionedCount) {
                            $fail("Ordered quantity cannot be lower than {$accessionedCount} because that many items have already been accessioned.");
                        }
                    }
                },
            ],
            'quantity_received'  => [
                'required',
                'integer',
                'min:0',
                'lte:quantity_ordered',
                function ($attribute, $value, $fail) {
                    if ($this->acquisitionIdBeingEdited) {
                        $accessionedCount = Accession::where('acquisition_id', $this->acquisitionIdBeingEdited)->count();

                        if ((int) $value < $accessionedCount) {
                            $fail("Received quantity cannot be lower than {$accessionedCount} because that many items have already been accessioned.");
                        }
                    }
                },
            ],
            'unit_cost'          => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'delivery_date'      => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addMonths(2)->format('Y-m-d')],
            'received_date'      => ['nullable', 'date', 'before_or_equal:today', 'required_if:status,delivered,partial'],
            'remarks'            => ['nullable', 'string'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'transaction_number.max'        => 'Transaction number must not exceed 50 characters.',
            'delivery_type.in'              => 'Selected delivery type must be either purchase or donation.',
            'quantity_received.lte'         => 'Received quantity cannot exceed ordered quantity.',
            'delivery_date.required'        => 'Expected delivery date is required.',
            'delivery_date.after_or_equal'  => 'The expected delivery date must be today or a future date.',
            'delivery_date.before_or_equal' => 'The expected delivery date cannot be more than 2 months from today.',
            'received_date.required_if'     => 'Received date is required when the acquisition is marked as delivered or partial.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'transaction_number' => 'Transaction / Invoice #',
            'delivery_type'      => 'Delivery Type',
            'status'             => 'Fulfillment Status',
            'vendor_id'          => 'Vendor',
            'catalog_id'         => 'Catalog Item',
            'quantity_ordered'   => 'Quantity Ordered',
            'quantity_received'  => 'Quantity Received',
            'unit_cost'          => 'Unit Cost',
            'delivery_date'      => 'Expected Delivery Date',
            'received_date'      => 'Received Date',
            'remarks'            => 'Remarks',
        ];
    }

    public function updatedVendorId($value): void
    {
        if ($this->acquisitionIdBeingEdited) {
            return;
        }
        $this->vendor_id = blank($value) ? null : (int) $value;
    }

    public function updatedCatalogId($value): void
    {
        if ($this->acquisitionIdBeingEdited) {
            return;
        }
        $this->catalog_id = blank($value) ? null : (int) $value;
    }

    public function updatedDeliveryType($value): void
    {
        if ($this->acquisitionIdBeingEdited) {
            return;
        }

        if ($value === 'donation') {
            $this->unit_cost = 0.0;
            $this->status = 'delivered';
            $this->quantity_received = (int) $this->quantity_ordered;
            $this->received_date = $this->received_date ?? now()->format('Y-m-d');
        }
    }

    public function updatedQuantityOrdered($value): void
    {
        if ($this->delivery_type === 'donation') {
            $this->quantity_received = (int) $value;
        }
    }

    public function updatedQuantityReceived($value): void
    {
        $received = (int) $value;
        $ordered = (int) $this->quantity_ordered;

        if ($received === 0) {
            $this->status = 'pending';
        } elseif ($received < $ordered) {
            $this->status = 'partial';
        } else {
            $this->status = 'delivered';
            $this->received_date = $this->received_date ?? now()->format('Y-m-d');
        }
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    #[Computed]
    public function selectedVendor(): ?Vendor
    {
        return $this->vendor_id ? Vendor::find($this->vendor_id) : null;
    }

    #[Computed]
    public function selectedCatalog(): ?Catalog
    {
        return $this->catalog_id ? Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])->find($this->catalog_id) : null;
    }

    #[Computed]
    public function vendors()
    {
        try {
            return Vendor::orderBy('company_name')
                ->get([
                    'id',
                    'company_name',
                    'contact_first_name',
                    'contact_middle_name',
                    'contact_last_name',
                    'contact_email',
                    'contact_number',
                    'company_address',
                ])
                ->map(function ($vendor) {
                    $vendor->company_name = mb_convert_encoding((string) $vendor->company_name, 'UTF-8', 'UTF-8');

                    return $vendor;
                });
        } catch (Throwable $e) {
            Log::error('Error loading vendors for acquisitions: ' . $e->getMessage());

            return collect();
        }
    }

    #[Computed]
    public function catalogs()
    {
        try {
            return Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
                ->orderBy('title')
                ->get()
                ->map(function ($catalog) {
                    $catalog->title = mb_convert_encoding((string) $catalog->title, 'UTF-8', 'UTF-8');

                    return $catalog;
                });
        } catch (Throwable $e) {
            Log::error('Error loading catalogs for acquisitions: ' . $e->getMessage());

            return collect();
        }
    }

    #[Computed]
    public function calculatedTotalCost(): float
    {
        $qty = (int) ($this->quantity_ordered ?? 0);
        $cost = (float) ($this->unit_cost ?? 0);

        return round($qty * $cost, 2);
    }

    public function openCreateModal(): void
    {
        try {
            $this->resetValidation();
            $this->resetForm();

            $this->acquisition_number = $this->generateAcquisitionNumber();
            $this->delivery_type = 'purchase';
            $this->status = 'pending';
            $this->quantity_ordered = 1;
            $this->quantity_received = 0;
            $this->delivery_date = now()->format('Y-m-d');
            $this->received_date = null;
            $this->showModal = true;
        } catch (Throwable $e) {
            Log::error('Error opening create acquisition modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to initialize new acquisition form.', type: 'error');
        }
    }

    public function addMoreFromExisting(int $id): void
    {
        try {
            $this->resetValidation();
            $existing = Acquisition::findOrFail($id);

            $this->resetForm();

            $this->vendor_id = $existing->vendor_id;
            $this->transaction_number = mb_convert_encoding((string) $existing->transaction_number, 'UTF-8', 'UTF-8');
            $this->delivery_type = $existing->delivery_type;
            $this->delivery_date = $existing->delivery_date ? $existing->delivery_date->format('Y-m-d') : now()->format('Y-m-d');

            $this->status = 'pending';
            $this->quantity_ordered = 1;
            $this->quantity_received = 0;
            $this->received_date = null;

            $this->acquisition_number = $this->generateAcquisitionNumber();
            $this->showModal = true;
        } catch (Throwable $e) {
            Log::error('Error preparing follow-up acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load existing acquisition details.', type: 'error');
        }
    }

    public function viewAccessions(int $id): void
    {
        try {
            $this->viewingAcquisition = Acquisition::with(['catalog', 'accessions'])->findOrFail($id);
            $this->showAccessionsModal = true;
        } catch (Throwable $e) {
            Log::error('Error loading accessions for acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load accessioned items for this acquisition.', type: 'error');
        }
    }

    private function generateAcquisitionNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $prefix = "ACQ-{$year}-";

            $latest = Acquisition::whereYear('created_at', $year)
                ->where('acquisition_number', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $baseNum = 0;
            if ($latest && preg_match('/-(\d+)$/', $latest->acquisition_number, $matches)) {
                $baseNum = (int) $matches[1];
            }

            $nextNum = str_pad((string) ($baseNum + 1), 3, '0', STR_PAD_LEFT);

            return $prefix . $nextNum;
        });
    }

    public function openEditModal(int $id): void
    {
        try {
            $this->resetValidation();
            $this->acquisitionIdBeingEdited = $id;

            $acq = Acquisition::findOrFail($id);

            $this->acquisition_number = mb_convert_encoding((string) $acq->acquisition_number, 'UTF-8', 'UTF-8');
            $this->transaction_number = mb_convert_encoding((string) $acq->transaction_number, 'UTF-8', 'UTF-8');
            $this->status = $acq->status;
            $this->delivery_type = $acq->delivery_type;
            $this->vendor_id = $acq->vendor_id;
            $this->catalog_id = $acq->catalog_id;
            $this->quantity_ordered = $acq->quantity_ordered;
            $this->quantity_received = $acq->quantity_received;
            $this->unit_cost = $acq->unit_cost;
            $this->delivery_date = $acq->delivery_date ? $acq->delivery_date->format('Y-m-d') : null;
            $this->received_date = $acq->received_date ? $acq->received_date->format('Y-m-d') : null;
            $this->remarks = $acq->remarks ? mb_convert_encoding((string) $acq->remarks, 'UTF-8', 'UTF-8') : null;

            $this->showModal = true;
        } catch (Throwable $e) {
            Log::error('Error opening edit acquisition modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load acquisition record for editing.', type: 'error');
        }
    }

    private function persistAcquisition(bool $isEditing): array
    {
        $this->cleanFields(['transaction_number', 'remarks']);

        $validated = $this->validate(
            $this->validationRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        if ($validated['delivery_type'] === 'donation') {
            $validated['status'] = 'delivered';
            $validated['quantity_received'] = (int) $validated['quantity_ordered'];
            $validated['received_date'] = $validated['received_date'] ?? now()->format('Y-m-d');
            if (! $isEditing) {
                $validated['unit_cost'] = 0.0;
            }
        } elseif ((int) $validated['quantity_received'] === 0) {
            $validated['status'] = 'pending';
            $validated['received_date'] = null;
        } elseif ((int) $validated['quantity_received'] < (int) $validated['quantity_ordered']) {
            $validated['status'] = 'partial';
        } else {
            $validated['status'] = 'delivered';
            $validated['received_date'] = $validated['received_date'] ?? now()->format('Y-m-d');
        }

        $maxAttempts = $isEditing ? 1 : 3;
        $saved = false;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($validated, $isEditing) {
                    if ($isEditing) {
                        $acq = Acquisition::where('id', $this->acquisitionIdBeingEdited)
                            ->lockForUpdate()
                            ->firstOrFail();

                        $acq->update([
                            'quantity_ordered'  => $validated['quantity_ordered'],
                            'quantity_received' => $validated['quantity_received'],
                            'status'            => $validated['status'],
                            'delivery_date'     => $validated['delivery_date'],
                            'received_date'     => $validated['received_date'],
                            'remarks'           => $validated['remarks'],
                        ]);
                    } else {
                        $exists = Acquisition::where('transaction_number', $validated['transaction_number'])
                            ->where('catalog_id', $this->catalog_id)
                            ->where('vendor_id', $this->vendor_id)
                            ->lockForUpdate()
                            ->exists();

                        if ($exists) {
                            throw ValidationException::withMessages([
                                'catalog_id' => 'This catalog item is already included under this invoice/transaction number for this vendor.',
                            ]);
                        }

                        $validated['acquisition_number'] = $this->generateAcquisitionNumber();
                        Acquisition::create($validated);
                    }
                });

                $saved = true;
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($isEditing || $attempt >= $maxAttempts) {
                    throw ValidationException::withMessages([
                        'transaction_number' => 'A database conflict occurred while saving this record. Please try again.',
                    ]);
                }

                continue;
            }
        }

        if (! $saved) {
            throw ValidationException::withMessages([
                'transaction_number' => 'Unable to save this record after multiple attempts. Please try again.',
            ]);
        }

        return $validated;
    }

    public function saveAcquisition(): void
    {
        $isEditing = (bool) $this->acquisitionIdBeingEdited;

        try {
            $this->persistAcquisition($isEditing);

            $message = $isEditing
                ? 'Acquisition record updated successfully.'
                : 'Acquisition record created successfully.';

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error saving acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to save acquisition record right now.', type: 'error');
        }
    }

    public function saveAndCreateAnother(): void
    {
        if ($this->acquisitionIdBeingEdited) {
            return;
        }

        try {
            $validated = $this->persistAcquisition(false);

            $savedVendorId = $this->vendor_id;
            $savedTxn = $this->transaction_number;
            $savedDeliveryType = $this->delivery_type;
            $savedStatus = $validated['status'];
            $savedDeliveryDate = $this->delivery_date;
            $savedDate = $validated['received_date'];

            $this->resetForm();

            $this->vendor_id = $savedVendorId;
            $this->transaction_number = $savedTxn;
            $this->delivery_type = $savedDeliveryType;
            $this->status = $savedStatus;
            $this->delivery_date = $savedDeliveryDate;
            $this->received_date = $savedDate;
            $this->acquisition_number = $this->generateAcquisitionNumber();
            $this->showModal = true;

            $this->dispatch('toast', message: 'Item saved successfully. Ready for the next item on this invoice.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error in saveAndCreateAnother acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to save acquisition record right now.', type: 'error');
        }
    }

    public function confirmDelete(int $id): void
    {
        try {
            $hasAccessions = Accession::where('acquisition_id', $id)->exists();

            if ($hasAccessions) {
                $this->dispatch('toast', message: 'Cannot delete: Acquisition has active accessioned items attached.', type: 'error');

                return;
            }

            $this->acquisitionIdBeingDeleted = $id;
            $this->showDeleteModal = true;
        } catch (Throwable $e) {
            Log::error('Error checking accessions before delete: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to verify acquisition dependencies.', type: 'error');
        }
    }

    public function deleteAcquisition(): void
    {
        if (! $this->acquisitionIdBeingDeleted) {
            $this->showDeleteModal = false;

            return;
        }

        try {
            DB::transaction(function () {
                $acq = Acquisition::where('id', $this->acquisitionIdBeingDeleted)
                    ->lockForUpdate()
                    ->first();

                if (! $acq) {
                    return;
                }

                if (Accession::where('acquisition_id', $acq->id)->exists()) {
                    throw ValidationException::withMessages([
                        'delete' => 'Deletion blocked: Accession records exist for this acquisition.',
                    ]);
                }

                $acq->delete();
            });

            $this->dispatch('toast', message: 'Acquisition record deleted successfully.', type: 'success');
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: 'Deletion blocked: Accession records exist for this acquisition.', type: 'error');
        } catch (QueryException $e) {
            Log::error('QueryException deleting acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot delete: Acquisition is referenced by other system records.', type: 'error');
        } catch (Throwable $e) {
            Log::error('Error deleting acquisition: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to delete acquisition record right now.', type: 'error');
        }

        $this->showDeleteModal = false;
        $this->acquisitionIdBeingDeleted = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'acquisition_number',
            'transaction_number',
            'status',
            'delivery_type',
            'vendor_id',
            'catalog_id',
            'quantity_ordered',
            'quantity_received',
            'unit_cost',
            'delivery_date',
            'received_date',
            'remarks',
            'acquisitionIdBeingEdited',
        ]);
        $this->quantity_ordered = 1;
        $this->quantity_received = 0;
        $this->delivery_type = 'purchase';
        $this->status = 'pending';
    }

    private function filteredAcquisitionsQuery(): Builder
    {
        $likeOperator = $this->likeOperator();
        $searchTerm = strtolower(mb_convert_encoding(addcslashes(trim($this->search), '%_\\'), 'UTF-8', 'UTF-8'));

        return Acquisition::with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {
                $query->where(function ($q) use ($likeOperator, $searchTerm) {
                    $q->where('acquisition_number', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('transaction_number', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('status', $likeOperator, "%{$searchTerm}%")
                        ->orWhere('delivery_type', $likeOperator, "%{$searchTerm}%")
                        ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('catalog.author', fn ($sub) => $sub->where('name', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('catalog.assetType', fn ($sub) => $sub->where('name', $likeOperator, "%{$searchTerm}%"))
                        ->orWhereHas('vendor', fn ($sub) => $sub->where('company_name', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->latest();
    }

    public function exportExcel(): BinaryFileResponse|StreamedResponse|null
    {
        try {
            return Excel::download(
                new AcquisitionsExcelExport(trim($this->search)),
                'acquisitions-report-' . now()->format('Y-m-d_His') . '.xlsx'
            );
        } catch (Throwable $e) {
            Log::error('Error exporting acquisitions Excel: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export acquisitions Excel report.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        try {
            $acquisitions = $this->filteredAcquisitionsQuery()->get();

            $pdf = Pdf::loadView('pdf.acquisitions-report', [
                'acquisitions' => $acquisitions,
                'searchTerm'   => trim($this->search),
                'date'         => now(),
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'acquisitions-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (Throwable $e) {
            Log::error('Error exporting acquisitions PDF: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export acquisitions PDF report.', type: 'error');

            return null;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Acquisitions')]
    public function render()
    {
        try {
            $acquisitions = $this->filteredAcquisitionsQuery()->paginate(10);

            $acquisitions->getCollection()->transform(function ($acq) {
                $acq->acquisition_number = mb_convert_encoding((string) $acq->acquisition_number, 'UTF-8', 'UTF-8');
                $acq->transaction_number = mb_convert_encoding((string) $acq->transaction_number, 'UTF-8', 'UTF-8');
                if ($acq->remarks) {
                    $acq->remarks = mb_convert_encoding((string) $acq->remarks, 'UTF-8', 'UTF-8');
                }

                return $acq;
            });
        } catch (Throwable $e) {
            Log::error('Error rendering acquisitions list: ' . $e->getMessage());
            $acquisitions = Acquisition::query()->whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.acquisitions', [
            'acquisitions' => $acquisitions,
            'vendors'      => $this->vendors,
            'catalogs'     => $this->catalogs,
        ]);
    }
}
