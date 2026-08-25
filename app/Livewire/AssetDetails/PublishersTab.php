<?php

namespace App\Livewire\AssetDetails;

use App\Models\Publisher;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
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

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('publishers', 'name')->ignore($this->publisherIdBeingEdited),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'A publisher with this name already exists.',
            'name.min' => 'Publisher name must be at least 2 characters.',
            'name.max' => 'Publisher name must not exceed 255 characters.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'publisherIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(Publisher $publisher): void
    {
        $this->resetValidation();
        $this->publisherIdBeingEdited = $publisher->id;
        $this->name = $publisher->name;
        $this->showModal = true;
    }

    public function savePublisher(): void
    {
        $this->name = strtolower(trim($this->name));

        $this->validate();

        Publisher::updateOrCreate(
            ['id' => $this->publisherIdBeingEdited],
            ['name' => $this->name]
        );

        $message = $this->publisherIdBeingEdited
            ? 'Publisher updated successfully.'
            : 'Publisher created successfully.';

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
                // Catches FK foreign key restrictions (e.g., linked asset records)
                $this->dispatch('toast', message: 'Cannot delete: This publisher is linked to existing asset records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->publisherIdBeingDeleted = null;
    }

    public function render()
    {
        $publishers = Publisher::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'ilike', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.publishers-tab', [
            'publishers' => $publishers,
        ]);
    }
}
