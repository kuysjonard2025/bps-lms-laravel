<?php

namespace App\Livewire;

use App\Exports\AcquisitionsExport;
use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\Catalog;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Acquisitions extends Component
{
    use WithPagination;

    // Form Properties
    public ?string $acquisition_number = null;
    public string $transaction_number = '';
    public ?int $vendor_id = null;
    public ?int $catalog_id = null;
    public int $quantity = 1;
    public ?float $unit_cost = null;
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
            'transaction_number' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN\s\/._#-]+$/u'],
            'vendor_id'          => 'required|integer|exists:vendors,id',
            'catalog_id'         => 'required|integer|exists:catalogs,id',
            'quantity'           => [
                'required',
                'integer',
                'min:1',
                'max:100000',
                function ($attribute, $value, $fail) {
                    if ($this->acquisitionIdBeingEdited) {
                        $accessionedCount = Accession::where('acquisition_id', $this->acquisitionIdBeingEdited)->count();
                        if ($value < $accessionedCount) {
                            $fail("Quantity cannot be lower than the number of items already accessioned ({$accessionedCount}).");
                        }
                    }
                },
            ],
            'unit_cost'          => 'required|numeric|min:0|max:99999999.99',
            'received_date'      => 'required|date|before_or_equal:today',
            'remarks'            => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'transaction_number.regex' => 'Transaction number may only contain letters, numbers, spaces, and - / . #',
        ];
    }

    public function updatedVendorId($value): void
    {
        $this->vendor_id = blank($value) ? null : (int) $value;
    }

    public function updatedCatalogId($value): void
    {
        $this->catalog_id = blank($value) ? null : (int) $value;
    }

    public function updatedSearch(): void
    {
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
        return $this->catalog_id ? Catalog::with(['author', 'assetType', 'publisher'])->find($this->catalog_id) : null;
    }

    #[Computed]
    public function vendors()
    {
        return Vendor::orderBy('company_name')
            ->get(['id', 'company_name'])
            ->map(function ($vendor) {
                $vendor->company_name = mb_convert_encoding((string) $vendor->company_name, 'UTF-8', 'UTF-8');
                return $vendor;
            });
    }

    #[Computed]
    public function catalogs()
    {
        return Catalog::orderBy('title')
            ->get(['id', 'title'])
            ->map(function ($catalog) {
                $catalog->title = mb_convert_encoding((string) $catalog->title, 'UTF-8', 'UTF-8');
                return $catalog;
            });
    }

    #[Computed]
    public function calculatedTotalCost(): float
    {
        $qty = (int) ($this->quantity ?? 0);
        $cost = (float) ($this->unit_cost ?? 0);

        return round($qty * $cost, 2);
    }

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

        $nextNum = str_pad((string) ($baseNum + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $nextNum;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $this->acquisitionIdBeingEdited = $id;

        $acq = Acquisition::findOrFail($id);

        $this->acquisition_number = mb_convert_encoding((string) $acq->acquisition_number, 'UTF-8', 'UTF-8');
        $this->transaction_number = mb_convert_encoding((string) $acq->transaction_number, 'UTF-8', 'UTF-8');
        $this->vendor_id          = $acq->vendor_id;
        $this->catalog_id         = $acq->catalog_id;
        $this->quantity           = $acq->quantity;
        $this->unit_cost          = $acq->unit_cost;
        $this->received_date      = $acq->received_date ? $acq->received_date->format('Y-m-d') : null;
        $this->remarks            = $acq->remarks ? mb_convert_encoding((string) $acq->remarks, 'UTF-8', 'UTF-8') : null;

        $this->showModal = true;
    }

    public function saveAcquisition(): void
    {
        $this->transaction_number = mb_convert_encoding(strip_tags(trim($this->transaction_number ?? '')), 'UTF-8', 'UTF-8');

        $remarksTrimmed = mb_convert_encoding(strip_tags(trim((string) $this->remarks)), 'UTF-8', 'UTF-8');
        $this->remarks = $remarksTrimmed === '' ? null : $remarksTrimmed;

        $validated = $this->validate();
        $validated['transaction_number'] = strtoupper(trim($this->transaction_number));
        $validated['remarks'] = blank($this->remarks) ? null : strtolower(trim($this->remarks));

        $isEditing = (bool) $this->acquisitionIdBeingEdited;
        $maxAttempts = $isEditing ? 1 : 3;
        $saved = false;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($validated, $isEditing) {
                    $exists = Acquisition::where('transaction_number', $this->transaction_number)
                        ->where('catalog_id', $this->catalog_id)
                        ->where('vendor_id', $this->vendor_id)
                        ->when($isEditing, fn ($query) => $query->where('id', '!=', $this->acquisitionIdBeingEdited))
                        ->lockForUpdate()
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'transaction_number' => 'An acquisition record with this Transaction Number, Vendor, and Catalog item already exists.',
                        ]);
                    }

                    if ($isEditing) {
                        $acq = Acquisition::findOrFail($this->acquisitionIdBeingEdited);
                        $acq->update($validated);
                    } else {
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

        $message = $isEditing
            ? 'Acquisition record updated successfully.'
            : 'Acquisition record created successfully.';

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $hasAccessions = Accession::where('acquisition_id', $id)->exists();

        if ($hasAccessions) {
            $this->dispatch('toast', message: 'Cannot delete: Acquisition has active accessioned items attached.', type: 'error');
            return;
        }

        $this->acquisitionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
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
            $this->dispatch('toast', message: 'Cannot delete: Acquisition is referenced by other system records.', type: 'error');
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

    private function filteredAcquisitionsQuery(): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $searchTerm = mb_convert_encoding(addcslashes(trim($this->search), '%_\\'), 'UTF-8', 'UTF-8');

        return Acquisition::with(['catalog.author', 'catalog.assetType', 'vendor'])
            ->when($searchTerm !== '', function ($query) use ($likeOperator, $searchTerm) {
                $query->where(function ($q) use ($likeOperator, $searchTerm) {
                    $q->where('acquisition_number', $likeOperator, "%{$searchTerm}%")
                      ->orWhere('transaction_number', $likeOperator, "%{$searchTerm}%")
                      ->orWhereHas('catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$searchTerm}%"))
                      ->orWhereHas('vendor', fn ($sub) => $sub->where('company_name', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->latest();
    }

    public function exportExcel()
    {
        return Excel::download(
            new AcquisitionsExport(trim($this->search)),
            'acquisitions-report-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function exportPdf(): StreamedResponse
    {
        $acquisitions = $this->filteredAcquisitionsQuery()->get();

        $pdf = Pdf::loadView('pdf.acquisitions-report', [
            'acquisitions' => $acquisitions,
            'searchTerm'   => trim($this->search),
            'date'         => now(), // Send as Carbon instance
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'acquisitions-report-' . now()->format('Y-m-d') . '.pdf');
    }

    #[Layout('components.layouts.app')]
    #[Title('Acquisitions')]
    public function render()
    {
        $acquisitions = $this->filteredAcquisitionsQuery()->paginate(10);

        // Sanitize paginated string items before rendering
        $acquisitions->getCollection()->transform(function ($acq) {
            $acq->acquisition_number = mb_convert_encoding((string) $acq->acquisition_number, 'UTF-8', 'UTF-8');
            $acq->transaction_number = mb_convert_encoding((string) $acq->transaction_number, 'UTF-8', 'UTF-8');
            if ($acq->remarks) {
                $acq->remarks = mb_convert_encoding((string) $acq->remarks, 'UTF-8', 'UTF-8');
            }
            return $acq;
        });

        return view('livewire.acquisitions', [
            'acquisitions' => $acquisitions,
            'vendors'      => $this->vendors,
            'catalogs'     => $this->catalogs,
        ]);
    }
}
