<?php

namespace App\Livewire;

use App\Models\AssetType;
use App\Models\CirculationPolicy as PolicyModel;
use App\Models\PatronType;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public ?int $patron_type_id = null;
    public ?int $asset_type_id = null;
    public ?int $max_borrow_limit = 3;
    public ?int $loan_duration_days = 7;
    public ?int $max_renewals = 1;
    public ?int $grace_period_days = 0;
    public ?float $fine_per_day = 5.00;
    public ?float $max_fine_amount = 100.00;
    public bool $is_active = true;

    // Display property for read-only badge
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
            'patron_type_id' => [
                'required',
                'integer',
                Rule::exists('patron_types', 'id')->where(function ($query) {
                    $query->where('name', 'like', '%Student%');
                }),
                Rule::unique('circulation_policies', 'patron_type_id')
                    ->where(fn ($query) => $query->where('asset_type_id', $this->asset_type_id))
                    ->ignore($this->policy_id),
            ],
            'asset_type_id' => [
                'required',
                'integer',
                'exists:asset_types,id',
            ],
            'max_borrow_limit' => 'required|integer|min:1|max:100',
            'loan_duration_days' => 'required|integer|min:1|max:365',
            'max_renewals' => 'required|integer|min:0|max:10',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'fine_per_day' => 'required|numeric|min:0|max:9999.99',
            'max_fine_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:99999.99',
                'gte:fine_per_day',
            ],
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'patron_type_id.required' => 'The Student borrower type is required.',
            'patron_type_id.exists' => 'The selected borrower type must be a valid Student type.',
            'patron_type_id.unique' => 'A policy rule already exists for this Student Borrower Type and Asset Type pair.',
            'asset_type_id.required' => 'The asset type is required.',
            'asset_type_id.exists' => 'The selected asset type is invalid.',
            'max_fine_amount.gte' => 'The maximum fine amount must be greater than or equal to the daily fine.',
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
        $this->patron_type_id = $policy->patron_type_id;
        $this->asset_type_id = $policy->asset_type_id;
        $this->max_borrow_limit = $policy->max_borrow_limit;
        $this->loan_duration_days = $policy->loan_duration_days;
        $this->max_renewals = $policy->max_renewals;
        $this->grace_period_days = $policy->grace_period_days;
        $this->fine_per_day = (float) $policy->fine_per_day;
        $this->max_fine_amount = (float) $policy->max_fine_amount;
        $this->is_active = (bool) $policy->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        // Ensure patron_type_id is locked to Student before validation
        $this->resolveStudentPatronType();

        $validated = $this->validate();

        $payload = [
            'patron_type_id' => (int) $validated['patron_type_id'],
            'asset_type_id' => (int) $validated['asset_type_id'],
            'max_borrow_limit' => (int) $validated['max_borrow_limit'],
            'loan_duration_days' => (int) $validated['loan_duration_days'],
            'max_renewals' => (int) $validated['max_renewals'],
            'grace_period_days' => (int) $validated['grace_period_days'],
            'fine_per_day' => round((float) $validated['fine_per_day'], 2),
            'max_fine_amount' => round((float) $validated['max_fine_amount'], 2),
            'is_active' => (bool) $validated['is_active'],
        ];

        try {
            if ($this->policy_id) {
                $policy = PolicyModel::findOrFail($this->policy_id);
                $policy->update($payload);
                $message = 'Student circulation policy updated successfully.';
            } else {
                PolicyModel::create($payload);
                $message = 'Student circulation policy created successfully.';
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'patron_type_id' => 'A policy rule already exists for this Student Borrower Type and Asset Type pair.',
            ]);
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $policy = PolicyModel::findOrFail($id);
        $policy->update(['is_active' => ! $policy->is_active]);

        $this->dispatch('toast', message: 'Policy status updated.', type: 'success');
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
        $this->reset(['policy_id', 'asset_type_id', 'isEditing']);
        $this->resolveStudentPatronType();

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
    #[Title('Student Circulation Policy')]
    public function render(): View
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $policies = PolicyModel::with(['patronType', 'assetType'])
            ->whereHas('patronType', fn ($q) => $q->where('name', $likeOperator, '%Student%'))
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $likeOperator) {
                $query->whereHas('assetType', fn ($q) => $q->where('name', $likeOperator, "%{$searchTerm}%"));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.circulation-policy', [
            'policies' => $policies,
            'assetTypes' => AssetType::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
