<?php

namespace App\Livewire\AssetDetails;

use App\Models\AssetType;
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
            'name' => "required|string|max:255|unique:asset_types,name,{$this->assetTypeIdBeingEdited}",
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
        $this->validate();

        AssetType::updateOrCreate(
            ['id' => $this->assetTypeIdBeingEdited],
            ['name' => trim($this->name)]
        );

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
            AssetType::destroy($this->assetTypeIdBeingDeleted);
            $this->dispatch('toast', message: 'Asset type deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->assetTypeIdBeingDeleted = null;
    }

    public function render()
    {
        $assetTypes = AssetType::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.asset-types-tab', [
            'assetTypes' => $assetTypes,
        ]);
    }
}
