<?php

namespace App\Livewire;

use App\Models\AssetType;
use App\Models\CirculationPolicy as PolicyModel;
use App\Models\PatronType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class CirculationPolicy extends Component
{
    use WithPagination;

    // Form inputs
    public ?int $policy_id = null;
    public ?int $patron_type_id = null;
    public ?int $asset_type_id = null;
    public int $max_borrow_limit = 3;
    public int $loan_duration_days = 7;
    public int $max_renewals = 1;
    public int $grace_period_days = 0;
    public float $fine_per_day = 5.00;
    public float $max_fine_amount = 100.00;
    public bool $is_active = true;

    // UI Controls
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'patron_type_id' => 'required|exists:patron_types,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'max_borrow_limit' => 'required|integer|min:1|max:100',
            'loan_duration_days' => 'required|integer|min:1|max:365',
            'max_renewals' => 'required|integer|min:0|max:10',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'fine_per_day' => 'required|numeric|min:0|max:9999.99',
            'max_fine_amount' => 'required|numeric|min:0|max:99999.99',
            'is_active' => 'boolean',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editPolicy(int $id): void
    {
        $policy = PolicyModel::findOrFail($id);

        $this->policy_id = $policy->id;
        $this->patron_type_id = $policy->patron_type_id;
        $this->asset_type_id = $policy->asset_type_id;
        $this->max_borrow_limit = $policy->max_borrow_limit;
        $this->loan_duration_days = $policy->loan_duration_days;
        $this->max_renewals = $policy->max_renewals;
        $this->grace_period_days = $policy->grace_period_days;
        $this->fine_per_day = (float) $policy->fine_per_day;
        $this->max_fine_amount = (float) $policy->max_fine_amount;
        $this->is_active = $policy->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Enforce unique constraint: ['patron_type_id', 'asset_type_id']
        $exists = PolicyModel::where('patron_type_id', $this->patron_type_id)
            ->where('asset_type_id', $this->asset_type_id)
            ->when($this->isEditing, fn ($q) => $q->where('id', '!=', $this->policy_id))
            ->exists();

        if ($exists) {
            $this->addError('patron_type_id', 'A policy rule already exists for this Patron Type and Asset Type pair.');
            return;
        }

        PolicyModel::updateOrCreate(
            ['id' => $this->policy_id],
            [
                'patron_type_id' => $this->patron_type_id,
                'asset_type_id' => $this->asset_type_id,
                'max_borrow_limit' => $this->max_borrow_limit,
                'loan_duration_days' => $this->loan_duration_days,
                'max_renewals' => $this->max_renewals,
                'grace_period_days' => $this->grace_period_days,
                'fine_per_day' => $this->fine_per_day,
                'max_fine_amount' => $this->max_fine_amount,
                'is_active' => $this->is_active,
            ]
        );

        session()->flash('message', $this->isEditing ? 'Policy updated successfully.' : 'Policy rule created successfully.');
        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $policy = PolicyModel::findOrFail($id);
        $policy->update(['is_active' => !$policy->is_active]);
        session()->flash('message', 'Policy status updated.');
    }

    public function deletePolicy(int $id): void
    {
        PolicyModel::findOrFail($id)->delete();
        session()->flash('message', 'Policy rule deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['policy_id', 'patron_type_id', 'asset_type_id', 'isEditing']);
        $this->max_borrow_limit = 3;
        $this->loan_duration_days = 7;
        $this->max_renewals = 1;
        $this->grace_period_days = 0;
        $this->fine_per_day = 5.00;
        $this->max_fine_amount = 100.00;
        $this->is_active = true;
        $this->resetValidation();
    }

    #[Layout('components.layouts.app')]
    #[Title('Circulation Policy')]
    public function render()
    {
        $policies = PolicyModel::with(['patronType', 'assetType'])
            ->when($this->search, function ($query) {
                $query->whereHas('patronType', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('assetType', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.circulation-policy', [
            'policies' => $policies,
            'patronTypes' => PatronType::orderBy('name')->get(),
            'assetTypes' => AssetType::orderBy('name')->get(),
        ]);
    }
}
