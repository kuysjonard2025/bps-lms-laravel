<?php

namespace App\Livewire;

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
    use WithPagination;

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
    public string $isbn_issn = '';
    public string $edition = '';
    public ?int $publication_year = null;
    public string $description = '';

    protected function rules(): array
    {
        return [
            'author_id'            => 'required|exists:authors,id',
            'asset_type_id'        => 'required|exists:asset_types,id',
            'publisher_id'         => 'required|exists:publishers,id',
            'general_reference_id' => 'required|exists:general_references,id',
            'title'                => 'required|string|max:255',
            'isbn_issn'            => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('catalogs', 'isbn_issn')->ignore($this->catalogIdBeingEdited),
            ],
            'edition'              => 'nullable|string|max:20',
            'publication_year'     => 'required|integer|digits:4|min:1800|max:' . (date('Y') + 1),
            'description'          => 'nullable|string|max:1000',
        ];
    }

    // Sanitize dropdown selection inputs
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
        $this->publisher_id        = $catalog->publisher_id;
        $this->general_reference_id = $catalog->general_reference_id;
        $this->title                = $catalog->title;
        $this->isbn_issn            = $catalog->isbn_issn ?? '';
        $this->edition              = $catalog->edition ?? '';
        $this->publication_year     = (int) $catalog->publication_year;
        $this->description          = $catalog->description ?? '';

        $this->showModal = true;
    }

    public function saveCatalog(): void
    {
        // Sanitize string fields prior to validation
        $this->title = trim($this->title);
        $this->isbn_issn = trim($this->isbn_issn);
        $this->edition = trim($this->edition);
        $this->description = trim($this->description);

        $validated = $this->validate();

        // Standardize nullable strings
        $payload = array_merge($validated, [
            'isbn_issn'   => $this->isbn_issn ?: null,
            'edition'     => $this->edition ?: null,
            'description' => $this->description ?: null,
        ]);

        if ($this->catalogIdBeingEdited) {
            $catalog = Catalog::findOrFail($this->catalogIdBeingEdited);
            $catalog->update($payload);
            $message = 'Catalog entry updated successfully.';
        } else {
            Catalog::create($payload);
            $message = 'Catalog entry created successfully.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $catalog = Catalog::find($id);

        if (!$catalog) {
            return;
        }

        // Prevent opening modal if active relationships exist
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
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $catalogs = Catalog::with(['author', 'assetType', 'publisher', 'generalReference'])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('title', $likeOperator, "%{$this->search}%")
                      ->orWhere('isbn_issn', $likeOperator, "%{$this->search}%")
                      ->orWhereHas('author', fn ($sub) => $sub->where('name', $likeOperator, "%{$this->search}%"))
                      ->orWhereHas('publisher', fn ($sub) => $sub->where('name', $likeOperator, "%{$this->search}%"));
                });
            })
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
