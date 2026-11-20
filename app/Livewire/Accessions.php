<?php

namespace App\Livewire;

use App\Exports\AccessionsExport;
use App\Exports\AccessionNumberExport;
use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Accession;
use App\Models\Acquisition;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class Accessions extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showExportModal = false;
    public bool $showBulkUpdateModal = false;
    public ?int $accessionIdBeingEdited = null;
    public ?int $accessionIdBeingDeleted = null;

    public ?string $exportStartAccession = null;
    public ?string $exportEndAccession = null;

    public ?int $acquisition_id = null;
    public string $accession_number = '';
    public string $condition = 'new';
    public string $status = 'stored';
    public ?string $remarks = null;

    public ?int $bulkAcquisitionId = null;
    public array $selectedAccessionIds = [];
    public string $bulkCondition = 'new';
    public string $bulkStatus = 'stored';
    public ?string $bulkRemarks = null;

    protected function rules(): array
    {
        $rules = [
            'acquisition_id' => 'required|exists:acquisitions,id',
            'condition'      => 'required|string|in:new,good,minor damaged,severe damaged,lost,missing',
            'status'         => ['required', 'string', 'in:available,on loan,stored,under maintenance,dumped'],
            'remarks'        => 'nullable|string|max:1000',
        ];

        if ($this->accessionIdBeingEdited) {
            $rules['accession_number'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('accessions', 'accession_number')->ignore($this->accessionIdBeingEdited),
            ];
        }

        return $rules;
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

        $existingCount = Accession::where('acquisition_id', $this->acquisition_id)
            ->when($this->accessionIdBeingEdited, fn($q) => $q->where('id', '!=', $this->accessionIdBeingEdited))
            ->count();

        return max(0, $acquisition->quantity_received - $existingCount);
    }

    #[Computed]
    public function selectedAcquisition(): ?Acquisition
    {
        if (! $this->acquisition_id) {
            return null;
        }

        return Acquisition::with([
            'catalog.author',
            'catalog.publisher',
            'catalog.generalReference',
            'catalog.assetType',
            'vendor',
        ])->find($this->acquisition_id);
    }

    #[Computed]
    public function bulkSelectedAccessions()
    {
        if (! $this->bulkAcquisitionId) {
            return collect();
        }

        return Accession::where('acquisition_id', $this->bulkAcquisitionId)
            ->orderBy('accession_number', 'asc')
            ->get();
    }

    public function updatedBulkAcquisitionId($value): void
    {
        $this->selectedAccessionIds = [];
        if ($value) {
            $this->selectedAccessionIds = Accession::where('acquisition_id', $value)
                ->where('status', '!=', 'on loan')
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        }
    }

    public function selectAllBulkItems(): void
    {
        if (! $this->bulkAcquisitionId) {
            return;
        }

        $this->selectedAccessionIds = Accession::where('acquisition_id', $this->bulkAcquisitionId)
            ->where('status', '!=', 'on loan')
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function deselectAllBulkItems(): void
    {
        $this->selectedAccessionIds = [];
    }

    public function updatedAcquisitionId($value): void
    {
        if ($this->accessionIdBeingEdited) {
            return;
        }

        if ($value) {
            $latestCopy = Accession::where('acquisition_id', (int) $value)
                ->get()
                ->map(function ($item) {
                    preg_match('/-(\d+)$/', $item->accession_number, $matches);
                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->max() ?? 0;

            $this->accession_number = $this->generateAccessionNumber((int) $value, $latestCopy + 1);
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
            'acquisition_id', 'accession_number',
            'condition', 'status', 'remarks', 'accessionIdBeingEdited',
        ]);

        $this->condition = 'new';
        $this->status = 'stored';
        $this->showModal = true;
    }

    public function openBulkUpdateModal(): void
    {
        $this->resetValidation();
        $this->reset(['bulkAcquisitionId', 'selectedAccessionIds', 'bulkCondition', 'bulkStatus', 'bulkRemarks']);
        $this->bulkCondition = 'new';
        $this->bulkStatus = 'stored';
        $this->showBulkUpdateModal = true;
    }

    private function generateAccessionNumber(int $acquisitionId, int $specificCopyNum): string
    {
        $year = date('Y');
        $batchStr = str_pad((string) $acquisitionId, 3, '0', STR_PAD_LEFT);
        $copyStr = str_pad((string) $specificCopyNum, 2, '0', STR_PAD_LEFT);

        return "ACC-{$year}-{$batchStr}-{$copyStr}";
    }

    public function openEditModal(Accession $accession): void
    {
        $this->resetValidation();
        $this->accessionIdBeingEdited = $accession->id;
        $this->acquisition_id = $accession->acquisition_id;
        $this->accession_number = $accession->accession_number;
        $this->condition = $accession->condition;
        $this->status = $accession->status;
        $this->remarks = $accession->remarks;
        $this->showModal = true;
    }

    public function saveAccession(): void
    {
        $fields = ['accession_number', 'remarks'];
        $this->cleanFields($fields);

        $this->validate();

        try {
            if ($this->accessionIdBeingEdited) {
                $accession = Accession::findOrFail($this->accessionIdBeingEdited);

                if ($accession->status === 'on loan') {
                    throw ValidationException::withMessages([
                        'status' => 'Cannot edit status/condition while item is currently on loan.',
                    ]);
                }

                $accession->update([
                    'acquisition_id'   => $this->acquisition_id,
                    'accession_number' => $this->accession_number,
                    'condition'        => $this->condition,
                    'status'           => $this->status,
                    'remarks'          => $this->remarks,
                ]);

                $message = 'Accession record updated successfully.';
            } else {
                DB::transaction(function () {
                    $acquisition = Acquisition::where('id', $this->acquisition_id)->lockForUpdate()->first();

                    $existingCount = Accession::where('acquisition_id', $this->acquisition_id)->count();
                    $remainingQty = max(0, $acquisition->quantity_received - $existingCount);

                    if ($remainingQty <= 0) {
                        throw ValidationException::withMessages([
                            'acquisition_id' => 'All received items for this acquisition have already been accessioned.',
                        ]);
                    }

                    $latestCopy = Accession::where('acquisition_id', $this->acquisition_id)
                        ->get()
                        ->map(function ($item) {
                            preg_match('/-(\d+)$/', $item->accession_number, $matches);
                            return isset($matches[1]) ? (int) $matches[1] : 0;
                        })
                        ->max() ?? 0;

                    $nextCopyNum = $latestCopy + 1;

                    for ($i = 0; $i < $remainingQty; $i++) {
                        $accessionNumber = $this->generateAccessionNumber($this->acquisition_id, $nextCopyNum);

                        while (Accession::where('accession_number', $accessionNumber)->exists()) {
                            $nextCopyNum++;
                            $accessionNumber = $this->generateAccessionNumber($this->acquisition_id, $nextCopyNum);
                        }

                        Accession::create([
                            'acquisition_id'   => $this->acquisition_id,
                            'accession_number' => $accessionNumber,
                            'condition'        => $this->condition,
                            'status'           => $this->status,
                            'remarks'          => $this->remarks,
                        ]);

                        $nextCopyNum++;
                    }
                });

                $message = 'Successfully generated all remaining accession barcodes for this acquisition.';
            }

            $this->showModal = false;
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (UniqueConstraintViolationException $e) {
            Log::warning('Accession number collision handled automatically for ACQ #' . $this->acquisition_id);
            $this->dispatch('toast', message: 'A barcode collision was detected. Please try clicking generate again.', type: 'error');
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('toast', message: $firstError ?? 'Please check the form for errors.', type: 'error');
            throw $e;
        } catch (Exception $e) {
            Log::error('Error saving accession: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while saving.', type: 'error');
        }
    }

    public function bulkUpdateAccessions(): void
    {
        $this->validate([
            'bulkAcquisitionId'    => 'required|exists:acquisitions,id',
            'selectedAccessionIds' => 'required|array|min:1',
            'selectedAccessionIds.*' => 'exists:accessions,id',
            'bulkCondition'        => 'required|string|in:new,good,minor damage,severe damaged,lost,missing',
            'bulkStatus'           => ['required', 'string', 'in:available,on loan,stored,under maintenance,dumped'],
            'bulkRemarks'          => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () {
                $query = Accession::whereIn('id', $this->selectedAccessionIds)
                    ->where('status', '!=', 'on loan');

                $updateData = [
                    'condition' => $this->bulkCondition,
                    'status'    => $this->bulkStatus,
                ];

                if (!empty($this->bulkRemarks)) {
                    $updateData['remarks'] = DB::raw("COALESCE(remarks, '') || ' | " . addslashes($this->bulkRemarks) . "'");
                }

                $query->update($updateData);
            });

            $this->showBulkUpdateModal = false;
            $this->reset(['bulkAcquisitionId', 'selectedAccessionIds', 'bulkCondition', 'bulkStatus', 'bulkRemarks']);
            $this->dispatch('toast', message: 'Selected accession items updated successfully.', type: 'success');
        } catch (Exception $e) {
            Log::error('Bulk update failed: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Failed to bulk update accession items.', type: 'error');
        }
    }

    public function confirmDelete(int $id): void
    {
        $accession = Accession::find($id);
        if ($accession && $accession->status === 'on loan') {
            $this->dispatch('toast', message: 'Cannot delete an item that is currently on loan.', type: 'error');
            return;
        }
        $this->accessionIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAccession(): void
    {
        if ($this->accessionIdBeingDeleted) {
            try {
                $accession = Accession::find($this->accessionIdBeingDeleted);
                if ($accession) {
                    if ($accession->status === 'on loan') {
                        $this->dispatch('toast', message: 'Deletion blocked: Item is currently checked out.', type: 'error');
                        $this->showDeleteModal = false;
                        $this->accessionIdBeingDeleted = null;
                        return;
                    }
                    $accession->delete();
                    $this->dispatch('toast', message: 'Accession item deleted successfully.', type: 'success');
                }
            } catch (Exception $e) {
                Log::error('Error deleting accession: ' . $e->getMessage());
                $this->dispatch('toast', message: 'Cannot delete: Item is referenced by circulation records.', type: 'error');
            }
        }
        $this->showDeleteModal = false;
        $this->accessionIdBeingDeleted = null;
    }

    private function getFilteredAccessionsQuery(string $orderBy = 'asc')
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Accession::with([
            'acquisition.vendor',
            'acquisition.catalog.author',
            'acquisition.catalog.publisher',
            'acquisition.catalog.generalReference',
            'acquisition.catalog.assetType'
        ])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('accession_number', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('acquisition.catalog', fn ($sub) => $sub->where('title', $likeOperator, "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('accession_number', $orderBy);
    }

    public function exportExcel(): ?BinaryFileResponse
    {
        return Excel::download(new AccessionsExport($this->search, $this->statusFilter), 'accessions-report-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function openExportModal(): void
    {
        $this->resetValidation();
        $this->exportStartAccession = null;
        $this->exportEndAccession = null;
        $this->showExportModal = true;
    }

    public function exportAccessionNumbers(): ?BinaryFileResponse
    {
        $this->showExportModal = false;
        return Excel::download(new AccessionNumberExport($this->search, $this->statusFilter, $this->exportStartAccession, $this->exportEndAccession), 'niimbot-accessions-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(): ?StreamedResponse
    {
        $accessions = $this->getFilteredAccessionsQuery('asc')->get();
        $pdf = Pdf::loadView('pdf.accessions-report', [
            'accessions' => $accessions,
            'filters'    => ['search' => $this->search, 'status' => $this->statusFilter],
            'date' => now()->format('F j, Y g:i A'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'accessions-report-' . now()->format('Y-m-d_His') . '.pdf');
    }

    #[Layout('components.layouts.app')]
    #[Title('Accessions')]
    public function render()
    {
        return view('livewire.accessions', [
            'accessions'   => $this->getFilteredAccessionsQuery()->paginate(10),
            'acquisitions' => Acquisition::with([
                'vendor',
                'catalog.author',
                'catalog.publisher',
                'catalog.generalReference',
                'catalog.assetType'
            ])->whereIn('status', ['partial', 'delivered'])->latest()->get(),
            'accessionedAcquisitions' => Acquisition::has('accessions')
                ->with(['catalog.author', 'accessions'])
                ->latest()
                ->get(),
        ]);
    }
}
