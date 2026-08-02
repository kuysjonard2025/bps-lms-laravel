<?php

namespace App\Livewire\AssetDetails;

use App\Models\GeneralReference;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('general_references', 'name')->ignore($this->referenceIdBeingEdited),
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
        // 1. Trim whitespace before running validation
        $this->name = trim($this->name);

        $this->validate();

        try {
            GeneralReference::updateOrCreate(
                ['id' => $this->referenceIdBeingEdited],
                ['name' => $this->name]
            );
        } catch (UniqueConstraintViolationException $e) {
            // Converts database constraint failures into inline field errors
            throw ValidationException::withMessages([
                'name' => 'A general reference with this name already exists.',
            ]);
        }

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
            try {
                $reference = GeneralReference::find($this->referenceIdBeingDeleted);

                if ($reference) {
                    $reference->delete();
                    $this->dispatch('toast', message: 'General reference deleted successfully.', type: 'success');
                }
            } catch (QueryException $e) {
                // Catches FK foreign key restrictions (e.g., PostgreSQL 23503)
                $this->dispatch('toast', message: 'Cannot delete: This reference is currently linked to existing asset records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->referenceIdBeingDeleted = null;
    }

    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $references = GeneralReference::query()
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where('name', $likeOperator, "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.general-references-tab', [
            'references' => $references,
        ]);
    }
}
