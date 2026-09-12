<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\AssetType;
use App\Models\CirculationPenalty;
use App\Models\PolicyLoanLimit;
use App\Models\PatronType;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CirculationPolicy extends Component
{
    use WithPagination, SanitizesInputs;

    public string $activeTab = 'circulation';

    // Circulation Policy Properties
    public ?int $policy_id = null;
    public string $name = '';
    public ?int $patron_type_id = null;
    public ?int $asset_type_id = null;
    public ?float $fine_per_day = 5.00;
    public ?float $max_fine_amount = 100.00;
    public bool $is_active = true;

    // Policy Loan Limit Properties (Single Record)
    public ?int $limit_id = null;
    public ?int $limit_patron_type_id = null;
    public ?int $max_borrow_limit = 3;
    public ?int $loan_duration_days = 7;

    public string $studentTypeName = 'Student';
    public bool $showModal = false;
    public bool $showLimitModal = false;
    public bool $isEditing = false;
    public string $search = '';

    public function mount(): void
    {
        try {
            $this->resolveStudentPatronType();
        } catch (\Exception $e) {
            $this->studentTypeName = 'Student';
        }
    }

    private function resolveStudentPatronType(): void
    {
        try {
            $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $studentType = PatronType::where('name', $likeOperator, '%student%')->first();
            if ($studentType) {
                $this->patron_type_id = $studentType->id;
                $this->limit_patron_type_id = $studentType->id;
                $this->studentTypeName = $studentType->name;
            }
        } catch (\Exception $e) {
            // Fallback silently
        }
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    protected function rules(): array
    {
        if ($this->activeTab === 'limits') {
            return [
                'limit_patron_type_id' => 'required|integer|exists:patron_types,id',
                'max_borrow_limit' => 'required|integer|min:1|max:100',
                'loan_duration_days' => 'required|integer|min:1|max:365',
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('circulation_penalties', 'name')->ignore($this->policy_id),
            ],
            'patron_type_id' => 'required|integer|exists:patron_types,id',
            'asset_type_id' => 'required|integer|exists:asset_types,id',
            'fine_per_day' => 'required|numeric|min:5|max:9999.99',
            'max_fine_amount' => 'required|numeric|min:50|max:99999.99|gte:fine_per_day',
            'is_active' => 'boolean',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->isEditing = false;

        if ($this->activeTab === 'limits') {
            $this->resetLimitForm();
            $this->showLimitModal = true;
        } else {
            $this->resetPolicyForm();
            $this->showModal = true;
        }
    }

    public function editPolicy(int $id): void
    {
        try {
            $this->resetValidation();
            $policy = CirculationPenalty::findOrFail($id);

            $this->policy_id = $policy->id;
            $this->name = $policy->name;
            $this->patron_type_id = $policy->patron_type_id;
            $this->asset_type_id = $policy->asset_type_id;
            $this->fine_per_day = (float) $policy->fine_per_day;
            $this->max_fine_amount = (float) $policy->max_fine_amount;
            $this->is_active = (bool) $policy->is_active;

            if ($policy->patronType && $policy->patronType->name) {
                $this->studentTypeName = $policy->patronType->name;
            }

            $this->isEditing = true;
            $this->showModal = true;
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', message: 'The selected policy record could not be found.', type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'An error occurred while loading the policy.', type: 'error');
        }
    }

    public function editLimit(?int $id = null): void
    {
        try {
            $this->resetValidation();
            $limit = $id ? PolicyLoanLimit::find($id) : PolicyLoanLimit::first();

            if ($limit) {
                $this->limit_id = $limit->id;
                $this->limit_patron_type_id = $limit->patron_type_id;
                $this->max_borrow_limit = $limit->max_borrow_limit;
                $this->loan_duration_days = $limit->loan_duration_days;

                if ($limit->patronType && $limit->patronType->name) {
                    $this->studentTypeName = $limit->patronType->name;
                }
                $this->isEditing = true;
            } else {
                $this->resetLimitForm();
                $this->isEditing = false;
            }

            $this->showLimitModal = true;
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Unable to load loan limit record.', type: 'error');
        }
    }

    public function save(): void
    {
        $this->resolveStudentPatronType();

        if ($this->activeTab === 'limits') {
            $this->cleanFields(['limit_patron_type_id', 'max_borrow_limit', 'loan_duration_days']);
            $validated = $this->validate();

            try {
                PolicyLoanLimit::updateOrCreate(
                    ['id' => $this->limit_id],
                    [
                        'patron_type_id' => (int) $this->limit_patron_type_id,
                        'max_borrow_limit' => (int) $validated['max_borrow_limit'],
                        'loan_duration_days' => (int) $validated['loan_duration_days'],
                    ]
                );
            } catch (QueryException $e) {
                Log::error('PolicyLoanLimit save database error', ['message' => $e->getMessage()]);
                $this->dispatch('toast', message: 'Database error occurred while saving.', type: 'error');
                return;
            }

            $message = $this->limit_id ? 'Loan limit updated successfully.' : 'Loan limit created successfully.';
            $this->closeLimitModal();
        } else {
            $fields = ['name', 'patron_type_id', 'asset_type_id', 'fine_per_day', 'max_fine_amount', 'is_active'];
            $this->cleanFields($fields);

            $validated = $this->validate();

            $payload = [
                'name' => $validated['name'],
                'patron_type_id' => (int) $this->patron_type_id,
                'asset_type_id' => (int) $validated['asset_type_id'],
                'fine_per_day' => round((float) $validated['fine_per_day'], 2),
                'max_fine_amount' => round((float) $validated['max_fine_amount'], 2),
                'is_active' => (bool) $validated['is_active'],
            ];

            try {
                if ($payload['is_active']) {
                    $this->deactivateOtherPolicies($payload['patron_type_id'], $payload['asset_type_id'], $this->policy_id);
                }

                if ($this->policy_id) {
                    CirculationPenalty::findOrFail($this->policy_id)->update($payload);
                    $message = 'Student circulation policy updated successfully.';
                } else {
                    CirculationPenalty::create($payload);
                    $message = 'Student circulation policy created successfully.';
                }
            } catch (QueryException $e) {
                Log::error('CirculationPolicy save database error', ['message' => $e->getMessage()]);
                $this->dispatch('toast', message: 'Database error occurred while saving.', type: 'error');
                return;
            }

            $this->closeModal();
        }

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        try {
            $policy = CirculationPenalty::findOrFail($id);
            $newStatus = ! $policy->is_active;

            if ($newStatus) {
                $this->deactivateOtherPolicies($policy->patron_type_id, $policy->asset_type_id, $policy->id);
            }

            $policy->update(['is_active' => $newStatus]);
            $this->dispatch('toast', message: 'Policy status updated.', type: 'success');
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', message: 'Policy record not found.', type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Could not update policy status.', type: 'error');
        }
    }

    private function deactivateOtherPolicies(int $patronTypeId, int $assetTypeId, ?int $currentPolicyId = null): void
    {
        try {
            CirculationPenalty::where('patron_type_id', $patronTypeId)
                ->where('asset_type_id', $assetTypeId)
                ->when($currentPolicyId, fn ($q) => $q->where('id', '!=', $currentPolicyId))
                ->update(['is_active' => false]);
        } catch (\Exception $e) {
            // Log or ignore safely to prevent disruption
        }
    }

    public function deletePolicy(int $id): void
    {
        try {
            $policy = CirculationPenalty::find($id);
            if ($policy) {
                $policy->delete();
                $this->dispatch('toast', message: 'Student policy rule deleted successfully.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'Policy record already removed.', type: 'error');
            }
        } catch (QueryException $e) {
            $this->dispatch('toast', message: 'Cannot delete: This policy is linked to existing transactions.', type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'An error occurred while deleting the policy.', type: 'error');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetPolicyForm();
        $this->resetValidation();
    }

    public function closeLimitModal(): void
    {
        $this->showLimitModal = false;
        $this->resetLimitForm();
        $this->resetValidation();
    }

    private function resetPolicyForm(): void
    {
        $this->reset(['policy_id', 'name', 'asset_type_id', 'isEditing']);
        $this->resolveStudentPatronType();
        $this->fine_per_day = 5.00;
        $this->max_fine_amount = 100.00;
        $this->is_active = true;
    }

    private function resetLimitForm(): void
    {
        $this->reset(['limit_id', 'isEditing']);
        $this->resolveStudentPatronType();
        $this->max_borrow_limit = 3;
        $this->loan_duration_days = 7;
    }

    #[Layout('components.layouts.app')]
    #[Title('Student Circulation Policy')]
    public function render(): View
    {
        $searchTerm = trim(strip_tags($this->search));
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $policies = new LengthAwarePaginator([], 0, 10);
        $limit = null;
        $assetTypes = collect();
        $patronTypes = collect();

        try {
            if ($this->activeTab === 'limits') {
                $limit = PolicyLoanLimit::with('patronType')->first();
            } else {
                $policies = CirculationPenalty::with(['patronType', 'assetType'])
                    ->whereHas('patronType', fn ($q) => $q->where('name', $likeOperator, '%Student%'))
                    ->when($searchTerm !== '', function ($query) use ($searchTerm, $likeOperator) {
                        $query->where(function ($q) use ($searchTerm, $likeOperator) {
                            $q->where('name', $likeOperator, "%{$searchTerm}%")
                                ->orWhereHas('assetType', fn ($a) => $a->where('name', $likeOperator, "%{$searchTerm}%"));
                        });
                    })
                    ->latest()
                    ->paginate(10);
            }

            $assetTypes = AssetType::orderBy('name')->get(['id', 'name']);
            $patronTypes = PatronType::orderBy('name')->get(['id', 'name']);
        } catch (\Exception $e) {
            // Prevents full system/page crash if database error occurs during render
        }

        return view('livewire.circulation-policy', [
            'policies' => $policies,
            'limit' => $limit,
            'assetTypes' => $assetTypes,
            'patronTypes' => $patronTypes,
        ]);
    }
}
