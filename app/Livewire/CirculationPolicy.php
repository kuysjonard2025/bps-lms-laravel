<?php

namespace App\Livewire;

use App\Models\AssetType;
use App\Models\CirculationPolicy as PolicyModel;
use App\Models\PatronType;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class CirculationPolicy extends Component
{
    use WithPagination;

    // Form attributes
    public ?int $policy_id = null;
    public string $name = '';
    public ?int $patron_type_id = null;
    public ?int $asset_type_id = null;
    public ?int $max_borrow_limit = 3;
    public ?int $loan_duration_days = 7;
    public ?float $fine_per_day = 5.00;
    public ?float $max_fine_amount = 100.00;
    public bool $is_active = true;

    // Display property
    public string $studentTypeName = 'Student';

    // UI state
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $search = '';

    public function mount(): void
    {
        $this->resolveStudentPatronType();
    }

    private function resolveStudentPatronType(): void
    {
        $studentType = PatronType::where('name', 'like', '%Student%')->first();
        if ($studentType) {
            $this->patron_type_id = $studentType->id;
            $this->studentTypeName = $studentType->name;
        }
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('circulation_policies', 'name')->ignore($this->policy_id),
            ],
            'patron_type_id' => 'required|integer|exists:patron_types,id',
            'asset_type_id' => 'required|integer|exists:asset_types,id',
            'max_borrow_limit' => 'required|integer|min:1|max:100',
            'loan_duration_days' => 'required|integer|min:1|max:365',
            'fine_per_day' => 'required|numeric|min:0|max:9999.99',
            'max_fine_amount' => 'required|numeric|min:0|max:99999.99|gte:fine_per_day',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'A circulation policy rule with this name already exists. Please choose a distinct name.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editPolicy(int $id): void
    {
        $this->resetValidation();
        $policy = PolicyModel::findOrFail($id);

        $this->policy_id = $policy->id;
        $this->name = $policy->name;
        $this->patron_type_id = $policy->patron_type_id;
        $this->asset_type_id = $policy->asset_type_id;
        $this->max_borrow_limit = $policy->max_borrow_limit;
        $this->loan_duration_days = $policy->loan_duration_days;
        $this->fine_per_day = (float) $policy->fine_per_day;
        $this->max_fine_amount = (float) $policy->max_fine_amount;
        $this->is_active = (bool) $policy->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->resolveStudentPatronType();
        $validated = $this->validate();

        $payload = [
            'name' => strtolower(trim($validated['name'])),
            'patron_type_id' => (int) $validated['patron_type_id'],
            'asset_type_id' => (int) $validated['asset_type_id'],
            'max_borrow_limit' => (int) $validated['max_borrow_limit'],
            'loan_duration_days' => (int) $validated['loan_duration_days'],
            'fine_per_day' => round((float) $validated['fine_per_day'], 2),
            'max_fine_amount' => round((float) $validated['max_fine_amount'], 2),
            'is_active' => (bool) $validated['is_active'],
        ];

        // Ensure only ONE active policy exists for this (patron_type_id, asset_type_id)
        if ($payload['is_active']) {
            $this->deactivateOtherPolicies($payload['patron_type_id'], $payload['asset_type_id'], $this->policy_id);
        }

        if ($this->policy_id) {
            PolicyModel::findOrFail($this->policy_id)->update($payload);
            $message = 'Student circulation policy updated successfully.';
        } else {
            PolicyModel::create($payload);
            $message = 'Student circulation policy created successfully.';
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $policy = PolicyModel::findOrFail($id);
        $newStatus = ! $policy->is_active;

        if ($newStatus) {
            // Deactivate all other policies for the same Borrower and Asset Type
            $this->deactivateOtherPolicies($policy->patron_type_id, $policy->asset_type_id, $policy->id);
        }

        $policy->update(['is_active' => $newStatus]);

        $this->dispatch('toast', message: 'Policy status updated.', type: 'success');
    }

    private function deactivateOtherPolicies(int $patronTypeId, int $assetTypeId, ?int $currentPolicyId = null): void
    {
        PolicyModel::where('patron_type_id', $patronTypeId)
            ->where('asset_type_id', $assetTypeId)
            ->when($currentPolicyId, fn ($q) => $q->where('id', '!=', $currentPolicyId))
            ->update(['is_active' => false]);
    }

    public function deletePolicy(int $id): void
    {
        try {
            $policy = PolicyModel::find($id);

            if ($policy) {
                $policy->delete();
                $this->dispatch('toast', message: 'Student policy rule deleted successfully.', type: 'success');
            }
        } catch (QueryException $e) {
            $this->dispatch('toast', message: 'Cannot delete: This policy is linked to existing transactions.', type: 'error');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['policy_id', 'name', 'asset_type_id', 'isEditing']);
        $this->resolveStudentPatronType();

        $this->max_borrow_limit = 3;
        $this->loan_duration_days = 7;
        $this->fine_per_day = 5.00;
        $this->max_fine_amount = 100.00;
        $this->is_active = true;
        $this->resetValidation();
    }

    #[Layout('components.layouts.app')]
    #[Title('Student Circulation Policy')]
    public function render(): View
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $policies = PolicyModel::with(['patronType', 'assetType'])
            ->whereHas('patronType', fn ($q) => $q->where('name', $likeOperator, '%Student%'))
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $likeOperator) {
                $query->where(function ($q) use ($searchTerm, $likeOperator) {
                    $q->where('name', $likeOperator, "%{$searchTerm}%")
                      ->orWhereHas('assetType', fn ($a) => $a->where('name', $likeOperator, "%{$searchTerm}%"));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.circulation-policy', [
            'policies' => $policies,
            'assetTypes' => AssetType::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
