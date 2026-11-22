<?php // app/Livewire/Catalogs.php

namespace App\Livewire;

use App\Exports\CatalogsExcelExport;
use App\Livewire\Helpers\SanitizesInputs;
use App\Models\AssetType;
use App\Models\Author;
use App\Models\Catalog;
use App\Models\GeneralReference;
use App\Models\Publisher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Catalogs extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $catalogIdBeingEdited = null;
    public ?int $catalogIdBeingDeleted = null;

    // Form inputs
    public ?int $author_id = null;
    public ?int $asset_type_id = null;
    public ?int $publisher_id = null;
    public ?int $general_reference_id = null;
    public string $title = '';
    public string $call_number = '';
    public ?string $isbn_issn = null;
    public ?string $edition = null;
    public ?int $publication_year = null;
    public ?string $description = null;

    // Searchable combobox query strings
    public string $authorSearch = '';
    public string $assetTypeSearch = '';
    public string $publisherSearch = '';
    public string $generalReferenceSearch = '';

    #[On('catalogs-imported')]
    public function handleCatalogsImported(): void
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        $fileName = 'catalogs-export-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CatalogsExcelExport($this->search), $fileName);
    }

    public function exportPdf()
    {
        $catalogs = Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
            ->when(trim($this->search) !== '', fn ($query) => $query->search(trim($this->search)))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.catalogs-report', [
            'catalogs' => $catalogs,
            'search'   => trim($this->search),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            'catalogs-report-' . now()->format('Y-m-d') . '.pdf',
        );
    }

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    private function nonAuthorAssetTypes(): array
    {
        return [
            'NEWSPAPER',
            'NEWSPAPERS',
            'MAGAZINE',
            'MAGAZINES',
            'JOURNAL',
            'JOURNALS',
            'PERIODICAL',
            'PERIODICALS',
            'SERIAL',
            'SERIALS',
        ];
    }

    public function getIsAuthorOptionalProperty(): bool
    {
        if (! $this->asset_type_id) {
            return false;
        }

        $typeName = strtoupper(trim((string) AssetType::whereKey($this->asset_type_id)->value('name')));

        return in_array($typeName, $this->nonAuthorAssetTypes(), true);
    }

    private function buildRules(): array
    {
        $authorRule = $this->isAuthorOptional
            ? ['nullable', 'exists:authors,id']
            : ['required', 'exists:authors,id'];

        return [
            'author_id'            => $authorRule,
            'asset_type_id'        => ['required', 'exists:asset_types,id'],
            'publisher_id'         => ['required', 'exists:publishers,id'],
            'general_reference_id' => ['required', 'exists:general_references,id'],
            'title'                => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('catalogs')->where(function ($query) {
                    $query->where('asset_type_id', $this->asset_type_id)
                        ->where('publisher_id', $this->publisher_id)
                        ->where('general_reference_id', $this->general_reference_id)
                        ->where('publication_year', $this->publication_year);

                    if ($this->author_id === null) {
                        $query->whereNull('author_id');
                    } else {
                        $query->where('author_id', $this->author_id);
                    }

                    return $query;
                })->ignore($this->catalogIdBeingEdited),
            ],
            'call_number'          => ['required', 'string', 'max:50'],
            'isbn_issn'            => [
                'nullable',
                'digits_between:8,20',
                Rule::unique('catalogs', 'isbn_issn')->ignore($this->catalogIdBeingEdited),
            ],
            'edition'              => ['nullable', 'string', 'max:50'],
            'publication_year'     => ['required', 'integer', 'digits:4', 'min:' . (date('Y') - 10), 'max:' . date('Y')],
            'description'          => ['nullable', 'string', 'max:255'],
        ];
    }

    private function validationAttributesMap(): array
    {
        return [
            'title'                => 'Title',
            'call_number'          => 'Call Number',
            'isbn_issn'            => 'ISBN/ISSN',
            'edition'              => 'Edition',
            'publication_year'     => 'Publication Year',
            'description'          => 'Description',
            'author_id'            => 'Author',
            'asset_type_id'        => 'Asset Type',
            'publisher_id'         => 'Publisher',
            'general_reference_id' => 'General Reference',
        ];
    }

    public function updatedAuthorId($value): void
    {
        if (blank($value)) {
            $this->author_id = null;
            $this->authorSearch = '';
        }
    }

    public function updatedAssetTypeId($value): void
    {
        if (blank($value)) {
            $this->asset_type_id = null;
            $this->assetTypeSearch = '';
            return;
        }

        if ($this->isAuthorOptional) {
            $this->author_id = null;
            $this->authorSearch = '';
        }
    }

    public function updatedPublisherId($value): void
    {
        if (blank($value)) {
            $this->publisher_id = null;
            $this->publisherSearch = '';
        }
    }

    public function updatedGeneralReferenceId($value): void
    {
        if (blank($value)) {
            $this->general_reference_id = null;
            $this->generalReferenceSearch = '';
        }
    }

    public function updatedIsbnIssn($value): void
    {
        if (blank($value)) {
            $this->isbn_issn = null;
        }
    }

    public function updatedEdition($value): void
    {
        if (blank($value)) {
            $this->edition = null;
        }
    }

    public function updatedDescription($value): void
    {
        if (blank($value)) {
            $this->description = null;
        }
    }

    public function selectAuthor(?int $id): void
    {
        if (! $id) {
            $this->author_id = null;
            $this->authorSearch = '';
            return;
        }

        $author = Author::find($id);
        if ($author) {
            $this->author_id = (int) $author->id;
            $this->authorSearch = (string) $author->name;
        }
    }

    public function selectAssetType(int $id): void
    {
        $type = AssetType::find($id);
        if (! $type) {
            return;
        }

        $this->asset_type_id = (int) $type->id;
        $this->assetTypeSearch = (string) $type->name;

        if ($this->isAuthorOptional) {
            $this->author_id = null;
            $this->authorSearch = '';
        }
    }

    public function selectPublisher(int $id): void
    {
        $publisher = Publisher::find($id);
        if ($publisher) {
            $this->publisher_id = (int) $publisher->id;
            $this->publisherSearch = (string) $publisher->name;
        }
    }

    public function selectGeneralReference(int $id): void
    {
        $ref = GeneralReference::find($id);
        if ($ref) {
            $this->general_reference_id = (int) $ref->id;
            $this->generalReferenceSearch = (string) $ref->name;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();

        $this->publication_year = (int) date('Y');
        $this->showModal = true;
    }

    public function openEditModal(Catalog $catalog): void
    {
        $catalog->loadMissing(['author', 'assetType', 'publisher', 'generalReference']);

        $this->resetValidation();
        $this->catalogIdBeingEdited = $catalog->id;
        $this->author_id            = $catalog->author_id;
        $this->asset_type_id        = $catalog->asset_type_id;
        $this->publisher_id         = $catalog->publisher_id;
        $this->general_reference_id = $catalog->general_reference_id;
        $this->title                = $catalog->title;
        $this->call_number          = $catalog->call_number ?? '';
        $this->isbn_issn            = $catalog->isbn_issn;
        $this->edition              = $catalog->edition;
        $this->publication_year     = (int) $catalog->publication_year;
        $this->description          = $catalog->description;

        $this->authorSearch           = (string) ($catalog->author?->name ?? '');
        $this->assetTypeSearch        = (string) ($catalog->assetType?->name ?? '');
        $this->publisherSearch        = (string) ($catalog->publisher?->name ?? '');
        $this->generalReferenceSearch = (string) ($catalog->generalReference?->name ?? '');

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function saveCatalog(): void
    {
        $this->cleanFields(['title', 'call_number', 'isbn_issn', 'edition', 'description']);

        $this->author_id = blank($this->author_id) ? null : (int) $this->author_id;
        $this->asset_type_id = blank($this->asset_type_id) ? null : (int) $this->asset_type_id;
        $this->publisher_id = blank($this->publisher_id) ? null : (int) $this->publisher_id;
        $this->general_reference_id = blank($this->general_reference_id) ? null : (int) $this->general_reference_id;
        $this->isbn_issn = blank($this->isbn_issn) ? null : $this->isbn_issn;
        $this->edition = blank($this->edition) ? null : $this->edition;
        $this->description = blank($this->description) ? null : $this->description;

        if ($this->isAuthorOptional) {
            $this->author_id = null;
        }

        $validated = $this->validate($this->buildRules(), [], $this->validationAttributesMap());

        if (blank($validated['isbn_issn'])) {
            $validated['isbn_issn'] = $this->generateUniqueIsbn();
        }

        if ($this->isAuthorOptional) {
            $validated['author_id'] = null;
        }

        try {
            $message = DB::transaction(function () use ($validated) {
                if ($this->catalogIdBeingEdited) {
                    $catalog = Catalog::findOrFail($this->catalogIdBeingEdited);
                    $catalog->update($validated);

                    return 'Catalog entry updated successfully.';
                }

                Catalog::create($validated);

                return 'Catalog entry created successfully.';
            });

            $this->closeModal();
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (Throwable $e) {
            Log::error('Failed saving catalog entry in Catalogs::saveCatalog: ' . $e->getMessage(), [
                'catalog_id' => $this->catalogIdBeingEdited,
                'exception'  => $e,
            ]);
            $this->dispatch('toast', message: 'Unable to save catalog entry right now.', type: 'error');
        }
    }

    private function generateUniqueIsbn(): string
    {
        do {
            $generated = '999' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Catalog::where('isbn_issn', $generated)->exists());

        return $generated;
    }

    public function confirmDelete(int $id): void
    {
        $catalog = Catalog::find($id);

        if (! $catalog) {
            return;
        }

        if ($catalog->acquisitions()->exists()) {
            $this->dispatch('toast', message: 'Cannot delete catalog item because associated acquisition records exist.', type: 'error');
            return;
        }

        $this->catalogIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCatalog(): void
    {
        if ($this->catalogIdBeingDeleted) {
            try {
                DB::transaction(function () {
                    $catalog = Catalog::lockForUpdate()->find($this->catalogIdBeingDeleted);

                    if (! $catalog) {
                        return;
                    }

                    if ($catalog->acquisitions()->exists()) {
                        $this->dispatch('toast', message: 'Deletion blocked: Active acquisition relations exist.', type: 'error');
                        return;
                    }

                    $catalog->delete();
                    $this->dispatch('toast', message: 'Catalog entry deleted successfully.', type: 'success');
                });
            } catch (QueryException $e) {
                Log::error('Catalog deletion query error: ' . $e->getMessage(), ['exception' => $e]);
                $this->dispatch('toast', message: 'Cannot delete catalog entry due to linked system relationships.', type: 'error');
            } catch (Throwable $e) {
                Log::error('Catalog deletion failed: ' . $e->getMessage(), ['exception' => $e]);
                $this->dispatch('toast', message: 'Unable to delete catalog entry right now.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->catalogIdBeingDeleted = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'author_id',
            'asset_type_id',
            'publisher_id',
            'general_reference_id',
            'title',
            'call_number',
            'isbn_issn',
            'edition',
            'publication_year',
            'description',
            'catalogIdBeingEdited',
            'authorSearch',
            'assetTypeSearch',
            'publisherSearch',
            'generalReferenceSearch',
        ]);
    }

    #[Layout('components.layouts.app')]
    #[Title('Catalogs')]
    public function render()
    {
        $op = $this->likeOperator();

        $catalogs = Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
            ->when(trim($this->search), fn ($query, $term) => $query->search($term))
            ->latest()
            ->paginate(10);

        $authors = Author::query()
            ->when(trim($this->authorSearch) !== '', fn ($q) => $q->where('name', $op, '%' . trim($this->authorSearch) . '%'))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name']);

        $assetTypes = AssetType::query()
            ->when(trim($this->assetTypeSearch) !== '', fn ($q) => $q->where('name', $op, '%' . trim($this->assetTypeSearch) . '%'))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name']);

        $publishers = Publisher::query()
            ->when(trim($this->publisherSearch) !== '', fn ($q) => $q->where('name', $op, '%' . trim($this->publisherSearch) . '%'))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name']);

        $generalReferences = GeneralReference::query()
            ->when(trim($this->generalReferenceSearch) !== '', fn ($q) => $q->where('name', $op, '%' . trim($this->generalReferenceSearch) . '%'))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name']);

        return view('livewire.catalogs', [
            'catalogs'          => $catalogs,
            'authors'           => $authors,
            'assetTypes'        => $assetTypes,
            'publishers'        => $publishers,
            'generalReferences' => $generalReferences,
        ]);
    }
}
