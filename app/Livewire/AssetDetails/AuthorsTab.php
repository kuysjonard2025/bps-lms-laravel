<?php

namespace App\Livewire\AssetDetails;

use App\Models\Author;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AuthorsTab extends Component
{
    use WithPagination;

    public string $search = '';

    // Modal state & form inputs
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $authorIdBeingEdited = null;
    public ?int $authorIdBeingDeleted = null;

    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('authors', 'name')->ignore($this->authorIdBeingEdited),
            ],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'authorIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(Author $author): void
    {
        $this->resetValidation();
        $this->authorIdBeingEdited = $author->id;
        $this->name = $author->name;
        $this->showModal = true;
    }

    public function saveAuthor(): void
    {
        $this->validate();

        Author::updateOrCreate(
            ['id' => $this->authorIdBeingEdited],
            [
                'name' => ucwords(trim($this->name)),
            ]
        );

        $message = $this->authorIdBeingEdited ? 'Author updated successfully.' : 'Author created successfully.';

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->authorIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAuthor(): void
    {
        if ($this->authorIdBeingDeleted) {
            Author::destroy($this->authorIdBeingDeleted);
            $this->dispatch('toast', message: 'Author deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->authorIdBeingDeleted = null;
    }

    public function render()
    {
        $authors = Author::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.authors-tab', [
            'authors' => $authors,
        ]);
    }
}
