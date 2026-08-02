<?php

namespace App\Livewire\AssetDetails;

use App\Models\Publisher;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PublishersTab extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $publisherIdBeingEdited = null;
    public ?int $publisherIdBeingDeleted = null;

    public string $name = '';
    public string $address = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('publishers', 'name')
                    ->where('address', $this->address)
                    ->ignore($this->publisherIdBeingEdited),
            ],
            'address' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'A publisher with this exact name and address combination already exists.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'address', 'publisherIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(Publisher $publisher): void
    {
        $this->resetValidation();
        $this->publisherIdBeingEdited = $publisher->id;
        $this->name = $publisher->name;
        $this->address = $publisher->address;
        $this->showModal = true;
    }

    public function savePublisher(): void
    {
        // 1. Clean inputs BEFORE validation so rules test the exact database payload
        $this->name = trim($this->name);
        $this->address = trim($this->address);

        $this->validate();

        try {
            Publisher::updateOrCreate(
                ['id' => $this->publisherIdBeingEdited],
                [
                    'name' => $this->name,
                    'address' => $this->address,
                ]
            );
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'name' => 'A publisher with this exact name and address combination already exists.',
            ]);
        }

        $message = $this->publisherIdBeingEdited ? 'Publisher updated successfully.' : 'Publisher created successfully.';

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->publisherIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deletePublisher(): void
    {
        if ($this->publisherIdBeingDeleted) {
            try {
                $publisher = Publisher::find($this->publisherIdBeingDeleted);

                if ($publisher) {
                    $publisher->delete();
                    $this->dispatch('toast', message: 'Publisher deleted successfully.', type: 'success');
                }
            } catch (QueryException $e) {
                // Catches FK foreign key restrictions (e.g., PostgreSQL 23503)
                $this->dispatch('toast', message: 'Cannot delete: This publisher is linked to existing asset records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->publisherIdBeingDeleted = null;
    }

    public function render()
    {
        // Use ilike for PostgreSQL (case-insensitive) or fallback to like for MySQL
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $publishers = Publisher::query()
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where('name', $likeOperator, "%{$this->search}%")
                      ->orWhere('address', $likeOperator, "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.publishers-tab', [
            'publishers' => $publishers,
        ]);
    }
}
