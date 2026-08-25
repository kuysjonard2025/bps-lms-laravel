<?php

namespace App\Livewire\AssetDetails;

use App\Models\Author;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                'min:2',
                'max:255',
                Rule::unique('authors', 'name')->ignore($this->authorIdBeingEdited),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'An author with this name already exists.',
            'name.min' => 'Author name must be at least 2 characters.',
            'name.max' => 'Author name must not exceed 255 characters.',
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
        // 1. Format FIRST so validation tests the exact string saved to DB
        $this->name = strtolower(trim($this->name));

        $this->validate();

        try {
            Author::updateOrCreate(
                ['id' => $this->authorIdBeingEdited],
                ['name' => $this->name]
            );
        } catch (UniqueConstraintViolationException $e) {
            // Converts database constraint failures into inline field errors
            throw ValidationException::withMessages([
                'name' => 'An author with this name already exists.',
            ]);
        }

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
            try {
                $author = Author::find($this->authorIdBeingDeleted);

                if ($author) {
                    $author->delete();
                    $this->dispatch('toast', message: 'Author deleted successfully.', type: 'success');
                }
            } catch (QueryException $e) {
                // Catches FK foreign key restrictions (e.g., PostgreSQL 23503)
                $this->dispatch('toast', message: 'Cannot delete: This author is linked to existing asset records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->authorIdBeingDeleted = null;
    }

    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $authors = Author::query()
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where('name', $likeOperator, "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.authors-tab', [
            'authors' => $authors,
        ]);
    }
}
