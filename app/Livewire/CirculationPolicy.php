<?php // app/Livewire/CirculationPolicy.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\AssetType;
use App\Models\CirculationPenalty;
use App\Models\PatronType;
use App\Models\PolicyLoanLimit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Circulation Policies')]
class CirculationPolicy extends Component
{
    use WithPagination, SanitizesInputs;

    public string $activeTab = 'circulation';

    // Circulation Policy Properties
    public ?int $policy_id = null;
    public ?int $patron_type_id = null;
    public ?int $asset_type_id = null;
    public ?int $lost_after_days = 14;

    // Student-Only Policy Loan Limit Properties
    public ?int $limit_id = null;
    public ?int $limit_patron_type_id = null;
    public ?int $max_borrow_limit = 3;
    public ?int $loan_duration_days = 7;

    public string $studentTypeName = 'Student';
    public bool $showModal = false;
    public bool $showLimitModal = false;
    public bool $showDeleteModal = false;
    public ?int $policyIdBeingDeleted = null;
    public bool $isEditing = false;
    public string $search = '';

    public function mount(): void
    {
        $this->resolveStudentPatronType();
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function resolveStudentPatronType(): void
    {
        try {
            $studentType = PatronType::where('name', $this->likeOperator(), '%student%')->first();
            if ($studentType) {
                $this->patron_type_id = $studentType->id;
                $this->limit_patron_type_id = $studentType->id;
                $this->studentTypeName = $studentType->name;
            }
        } catch (\Throwable $e) {
            $this->studentTypeName = 'Student';
        }
    }

    private function studentLoanLimitQuery()
    {
        $like = $this->likeOperator();

        return PolicyLoanLimit::with('patronType')
            ->whereHas('patronType', fn ($q) => $q->where('name', $like, '%student%'));
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        if (! in_array($tab, ['circulation', 'limits'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
    }

    protected function messages(): array
    {
        return [
            'asset_type_id.unique' => 'A penalty rule already exists for this asset type under the selected patron type.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();

        if ($this->activeTab === 'limits') {
            $this->editLimit();
            return;
        }

        $this->isEditing = false;
        $this->resetPolicyForm();
        $this->showModal = true;
    }

    public function editPolicy(int $id): void
    {
        try {
            $this->resetValidation();
            $policy = CirculationPenalty::with('patronType')->findOrFail($id);

            $this->policy_id = $policy->id;
            $this->patron_type_id = $policy->patron_type_id;
            $this->asset_type_id = $policy->asset_type_id;
            $this->lost_after_days = (int) $policy->lost_after_days;

            if ($policy->patronType?->name) {
                $this->studentTypeName = $policy->patronType->name;
            }

            $this->isEditing = true;
            $this->showModal = true;
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', message: 'The selected policy rule could not be found.', type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'An error occurred while loading the policy rule.', type: 'error');
        }
    }

    public function editLimit(?int $id = null): void
    {
        try {
            $this->resetValidation();
            $this->resolveStudentPatronType();

            $limit = $id
                ? $this->studentLoanLimitQuery()->where('id', $id)->first()
                : $this->studentLoanLimitQuery()->first();

            if ($limit) {
                $this->limit_id = $limit->id;
                $this->limit_patron_type_id = $limit->patron_type_id;
                $this->max_borrow_limit = (int) $limit->max_borrow_limit;
                $this->loan_duration_days = (int) $limit->loan_duration_days;

                if ($limit->patronType?->name) {
                    $this->studentTypeName = $limit->patronType->name;
                }
                $this->isEditing = true;
            } else {
                $this->resetLimitForm();
                $this->isEditing = false;
            }

            $this->showLimitModal = true;
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Unable to load student loan limit record.', type: 'error');
        }
    }

    public function save(): void
    {
        $this->resolveStudentPatronType();

        if (! $this->patron_type_id) {
            $this->dispatch('toast', message: 'Student patron type was not found.', type: 'error');
            return;
        }

        if ($this->activeTab === 'limits') {
            $this->limit_patron_type_id = $this->patron_type_id;
            $this->cleanFields(['limit_patron_type_id', 'max_borrow_limit', 'loan_duration_days']);

            $rules = [
                'limit_patron_type_id' => ['required', 'integer', 'exists:patron_types,id'],
                'max_borrow_limit' => ['required', 'integer', 'min:1', 'max:20'],
                'loan_duration_days' => ['required', 'integer', 'min:1', 'max:60'],
            ];

            $attributes = [
                'limit_patron_type_id' => 'patron type',
                'max_borrow_limit' => 'max borrow limit',
                'loan_duration_days' => 'loan duration',
            ];

            $validated = $this->validate($rules, $this->messages(), $attributes);

            try {
                $wasExisting = false;

                DB::transaction(function () use ($validated, &$wasExisting): void {
                    $existingLimit = $this->studentLoanLimitQuery()->lockForUpdate()->first();
                    $wasExisting = (bool) $existingLimit;

                    PolicyLoanLimit::updateOrCreate(
                        ['patron_type_id' => (int) $this->patron_type_id],
                        [
                            'max_borrow_limit' => (int) $validated['max_borrow_limit'],
                            'loan_duration_days' => (int) $validated['loan_duration_days'],
                        ]
                    );
                });
            } catch (QueryException $e) {
                Log::error('PolicyLoanLimit save database error', ['message' => $e->getMessage()]);
                $this->dispatch('toast', message: 'Database error occurred while saving student loan limit.', type: 'error');
                return;
            }

            $message = $wasExisting
                ? 'Student loan limit updated successfully.'
                : 'Student loan limit created successfully.';
            $this->closeLimitModal();
            $this->dispatch('toast', message: $message, type: 'success');
            return;
        }

        $this->cleanFields(['patron_type_id', 'asset_type_id', 'lost_after_days']);

        $patronTypeId = (int) ($this->patron_type_id ?? 0);

        $rules = [
            'patron_type_id' => ['required', 'integer', 'exists:patron_types,id'],
            'asset_type_id' => [
                'required',
                'integer',
                'exists:asset_types,id',
                Rule::unique('circulation_penalties', 'asset_type_id')
                    ->where(fn ($query) => $query->where('patron_type_id', $patronTypeId))
                    ->ignore($this->policy_id),
            ],
            'lost_after_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];

        $attributes = [
            'patron_type_id' => 'patron type',
            'asset_type_id' => 'asset type',
            'lost_after_days' => 'lost after overdue days',
        ];

        $validated = $this->validate($rules, $this->messages(), $attributes);

        $payload = [
            'patron_type_id' => (int) ($this->patron_type_id ?? $validated['patron_type_id']),
            'asset_type_id' => (int) $validated['asset_type_id'],
            'lost_after_days' => (int) $validated['lost_after_days'],
        ];

        try {
            $isUpdate = (bool) $this->policy_id;

            DB::transaction(function () use ($isUpdate, $payload): void {
                if ($isUpdate) {
                    $policy = CirculationPenalty::query()->lockForUpdate()->findOrFail($this->policy_id);
                    $policy->update($payload);
                } else {
                    CirculationPenalty::query()->create($payload);
                }
            });

            $message = $isUpdate
                ? 'Penalty rule updated successfully.'
                : 'Penalty rule created successfully.';
        } catch (QueryException $e) {
            Log::error('CirculationPolicy save database error', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'A rule for this asset type already exists or a database error occurred.', type: 'error');
            return;
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDeletePolicy(int $id): void
    {
        $this->policyIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['policyIdBeingDeleted']);
    }

    public function deletePolicy(): void
    {
        try {
            DB::transaction(function (): void {
                $policy = CirculationPenalty::query()->lockForUpdate()->find($this->policyIdBeingDeleted);

                if (! $policy) {
                    $this->dispatch('toast', message: 'Policy rule already removed.', type: 'error');
                    return;
                }

                $policy->delete();
                $this->dispatch('toast', message: 'Penalty rule deleted successfully.', type: 'success');
            });
        } catch (QueryException $e) {
            $this->dispatch('toast', message: 'Cannot delete: This rule is linked to existing transactions.', type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'An error occurred while deleting the rule.', type: 'error');
        }

        $this->closeDeleteModal();
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
        $this->reset(['policy_id', 'asset_type_id', 'isEditing']);
        $this->resolveStudentPatronType();
        $this->lost_after_days = 14;
    }

    private function resetLimitForm(): void
    {
        $this->reset(['limit_id', 'isEditing']);
        $this->resolveStudentPatronType();
        $this->max_borrow_limit = 3;
        $this->loan_duration_days = 7;
    }

    public function render(): View
    {
        $searchTerm = trim(strip_tags($this->search));
        $like = $this->likeOperator();

        $policies = new LengthAwarePaginator([], 0, 10);
        $limit = null;
        $assetTypes = collect();

        try {
            $limit = $this->studentLoanLimitQuery()->first();

            if ($this->activeTab === 'circulation') {
                $policies = CirculationPenalty::with(['patronType', 'assetType'])
                    ->whereHas('patronType', fn ($q) => $q->where('name', $like, '%student%'))
                    ->when($searchTerm !== '', function ($query) use ($searchTerm, $like) {
                        $query->whereHas('assetType', fn ($a) => $a->where('name', $like, "%{$searchTerm}%"));
                    })
                    ->latest()
                    ->paginate(10);
            }

            $assetTypes = AssetType::orderBy('name')->get(['id', 'name']);
        } catch (\Throwable $e) {
            Log::error('CirculationPolicy render error', ['message' => $e->getMessage()]);
        }

        return view('livewire.circulation-policy', [
            'policies' => $policies,
            'limit' => $limit,
            'assetTypes' => $assetTypes,
        ]);
    }
}
