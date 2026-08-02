<?php

namespace App\Livewire\AssetDetails;

use App\Models\AssetType;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class AssetTypesTab extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $assetTypeIdBeingEdited = null;
    public ?int $assetTypeIdBeingDeleted = null;

    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_types', 'name')->ignore($this->assetTypeIdBeingEdited),
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
        $this->reset(['name', 'assetTypeIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(AssetType $assetType): void
    {
        $this->resetValidation();
        $this->assetTypeIdBeingEdited = $assetType->id;
        $this->name = $assetType->name;
        $this->showModal = true;
    }

    public function saveAssetType(): void
    {
        // 1. Trim first so validation checks the exact string being saved
        $this->name = trim($this->name);

        $this->validate();

        try {
            AssetType::updateOrCreate(
                ['id' => $this->assetTypeIdBeingEdited],
                ['name' => $this->name]
            );
        } catch (UniqueConstraintViolationException $e) {
            // Safe fallback if database unique constraint fails
            throw ValidationException::withMessages([
                'name' => 'An asset type with this name already exists.',
            ]);
        }

        $message = $this->assetTypeIdBeingEdited ? 'Asset type updated successfully.' : 'Asset type created successfully.';

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->assetTypeIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAssetType(): void
    {
        if ($this->assetTypeIdBeingDeleted) {
            try {
                $assetType = AssetType::findOrFail($this->assetTypeIdBeingDeleted);
                $assetType->delete();

                $this->dispatch('toast', message: 'Asset type deleted successfully.', type: 'success');
            } catch (QueryException $e) {
                // Catches FK foreign key restriction violations (e.g., PostgreSQL 23503)
                $this->dispatch('toast', message: 'Cannot delete: This asset type is currently linked to existing records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->assetTypeIdBeingDeleted = null;
    }

    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $assetTypes = AssetType::query()
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where('name', $likeOperator, "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.asset-types-tab', [
            'assetTypes' => $assetTypes,
        ]);
    }
}
