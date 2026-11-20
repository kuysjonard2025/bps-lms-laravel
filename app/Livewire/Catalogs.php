<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\AssetType;
use App\Models\Author;
use App\Models\Catalog;
use App\Models\GeneralReference;
use App\Models\Publisher;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Catalogs extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $catalogIdBeingEdited = null;
    public ?int $catalogIdBeingDeleted = null;

    // Form inputs (Nullable types to match DB schema)
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

    protected function rules(): array
    {
        return [
            'author_id'            => ['required', 'exists:authors,id'],
            'asset_type_id'        => ['required', 'exists:asset_types,id'],
            'publisher_id'         => ['required', 'exists:publishers,id'],
            'general_reference_id' => ['required', 'exists:general_references,id'],
            'title'                => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('catalogs')->where(function ($query) {
                    return $query->where('author_id', $this->author_id)
                        ->where('asset_type_id', $this->asset_type_id)
                        ->where('publisher_id', $this->publisher_id)
                        ->where('general_reference_id', $this->general_reference_id)
                        ->where('publication_year', $this->publication_year);
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

    protected array $validationAttributes = [
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

    public function updatedAuthorId($value): void
    {
        if (blank($value)) $this->author_id = null;
    }

    public function updatedAssetTypeId($value): void
    {
        if (blank($value)) $this->asset_type_id = null;
    }

    public function updatedPublisherId($value): void
    {
        if (blank($value)) $this->publisher_id = null;
    }

    public function updatedGeneralReferenceId($value): void
    {
        if (blank($value)) $this->general_reference_id = null;
    }

    public function updatedIsbnIssn($value): void
    {
        if (blank($value)) $this->isbn_issn = null;
    }

    public function updatedEdition($value): void
    {
        if (blank($value)) $this->edition = null;
    }

    public function updatedDescription($value): void
    {
        if (blank($value)) $this->description = null;
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

        $this->isbn_issn = blank($this->isbn_issn) ? null : $this->isbn_issn;
        $this->edition = blank($this->edition) ? null : $this->edition;
        $this->description = blank($this->description) ? null : $this->description;

        $validated = $this->validate();

        if (blank($validated['isbn_issn'])) {
            $validated['isbn_issn'] = $this->generateUniqueIsbn();
        }

        if ($this->catalogIdBeingEdited) {
            Catalog::findOrFail($this->catalogIdBeingEdited)->update($validated);
            $message = 'Catalog entry updated successfully.';
        } else {
            Catalog::create($validated);
            $message = 'Catalog entry created successfully.';
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
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

        if (!$catalog) return;

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
                $catalog = Catalog::find($this->catalogIdBeingDeleted);

                if ($catalog) {
                    if ($catalog->acquisitions()->exists()) {
                        $this->dispatch('toast', message: 'Deletion blocked: Active acquisition relations exist.', type: 'error');
                        $this->showDeleteModal = false;
                        $this->catalogIdBeingDeleted = null;
                        return;
                    }

                    $catalog->delete();
                    $this->dispatch('toast', message: 'Catalog entry deleted successfully.', type: 'success');
                }
            } catch (QueryException $e) {
                $this->dispatch('toast', message: 'Cannot delete catalog entry due to linked system relationships.', type: 'error');
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
        ]);
    }

    #[Layout('components.layouts.app')]
    #[Title('Catalogs')]
    public function render()
    {
        $catalogs = Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
            ->when(trim($this->search), fn ($query, $term) => $query->search($term))
            ->latest()
            ->paginate(10);

        return view('livewire.catalogs', [
            'catalogs'          => $catalogs,
            'authors'           => Author::orderBy('name')->get(['id', 'name']),
            'assetTypes'        => AssetType::orderBy('name')->get(['id', 'name']),
            'publishers'        => Publisher::orderBy('name')->get(['id', 'name']),
            'generalReferences' => GeneralReference::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
