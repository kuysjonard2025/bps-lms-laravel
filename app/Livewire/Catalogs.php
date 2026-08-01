<?php

namespace App\Livewire;

use App\Models\AssetType;
use App\Models\Author;
use App\Models\Catalog;
use App\Models\GeneralReference;
use App\Models\Publisher;
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
            'author_id' => 'required|exists:authors,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'publisher_id' => 'required|exists:publishers,id',
            'general_reference_id' => 'required|exists:general_references,id',
            'title' => 'required|string|max:255',
            'isbn_issn' => 'nullable|string|max:20',
            'edition' => 'nullable|string|max:20',
            'publication_year' => 'required|integer|digits:4|min:1800|max:' . (date('Y') + 1),
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Convert empty string selections from select elements to null
    public function updatedAuthorId($value): void
    {
        if ($value === '') $this->author_id = null;
    }

    public function updatedAssetTypeId($value): void
    {
        if ($value === '') $this->asset_type_id = null;
    }

    public function updatedPublisherId($value): void
    {
        if ($value === '') $this->publisher_id = null;
    }

    public function updatedGeneralReferenceId($value): void
    {
        if ($value === '') $this->general_reference_id = null;
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
        $this->author_id = $catalog->author_id;
        $this->asset_type_id = $catalog->asset_type_id;
        $this->publisher_id = $catalog->publisher_id;
        $this->general_reference_id = $catalog->general_reference_id;
        $this->title = $catalog->title;
        $this->isbn_issn = $catalog->isbn_issn ?? '';
        $this->edition = $catalog->edition ?? '';
        $this->publication_year = (int) $catalog->publication_year;
        $this->description = $catalog->description ?? '';
        $this->showModal = true;
    }

    public function saveCatalog(): void
    {
        $this->validate();

        Catalog::updateOrCreate(
            ['id' => $this->catalogIdBeingEdited],
            [
                'author_id' => $this->author_id,
                'asset_type_id' => $this->asset_type_id,
                'publisher_id' => $this->publisher_id,
                'general_reference_id' => $this->general_reference_id,
                'title' => trim($this->title),
                'isbn_issn' => trim($this->isbn_issn) ?: null,
                'edition' => trim($this->edition) ?: null,
                'publication_year' => $this->publication_year,
                'description' => trim($this->description) ?: null,
            ]
        );

        $message = $this->catalogIdBeingEdited ? 'Catalog updated successfully.' : 'Catalog created successfully.';

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->catalogIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCatalog(): void
    {
        if ($this->catalogIdBeingDeleted) {
            Catalog::destroy($this->catalogIdBeingDeleted);
            $this->dispatch('toast', message: 'Catalog entry deleted successfully.', type: 'success');
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
            'catalogs' => $catalogs,
            'authors' => Author::orderBy('name')->get(),
            'assetTypes' => AssetType::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'generalReferences' => GeneralReference::orderBy('name')->get(),
        ]);
    }
}
