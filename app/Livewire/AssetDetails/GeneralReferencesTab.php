<?php

namespace App\Livewire\AssetDetails;

use App\Models\GeneralReference;
use Livewire\Component;
use Livewire\WithPagination;

class GeneralReferencesTab extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $referenceIdBeingEdited = null;
    public ?int $referenceIdBeingDeleted = null;

    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => "required|string|max:255|unique:general_references,name,{$this->referenceIdBeingEdited}",
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'referenceIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(GeneralReference $reference): void
    {
        $this->resetValidation();
        $this->referenceIdBeingEdited = $reference->id;
        $this->name = $reference->name;
        $this->showModal = true;
    }

    public function saveReference(): void
    {
        $this->validate();

        GeneralReference::updateOrCreate(
            ['id' => $this->referenceIdBeingEdited],
            ['name' => trim($this->name)]
        );

        $message = $this->referenceIdBeingEdited ? 'General reference updated successfully.' : 'General reference created successfully.';

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->referenceIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteReference(): void
    {
        if ($this->referenceIdBeingDeleted) {
            GeneralReference::destroy($this->referenceIdBeingDeleted);
            $this->dispatch('toast', message: 'General reference deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->referenceIdBeingDeleted = null;
    }

    public function render()
    {
        $references = GeneralReference::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.general-references-tab', [
            'references' => $references,
        ]);
    }
}
