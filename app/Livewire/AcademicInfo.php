<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Adviser;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Strand;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicInfo extends Component
{
    use WithPagination, SanitizesInputs;

    // 'grade_levels' | 'sections' | 'departments' | 'strands' | 'advisers'
    public string $activeTab = 'grade_levels';
    public string $modalType = 'grade_levels';

    public string $search = '';
    public string $sectionGradeFilter = '';
    public string $adviserDepartmentFilter = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $itemBeingDeleted = null;
    public string $deleteType = '';

    // Shared form fields
    public string $code = '';
    public string $name = '';
    public ?int $parent_id = null;
    public string $first_name = '';
    public ?string $middle_name = null;
    public string $last_name = '';
    public ?string $contact_number = null;
    public ?string $email = null;

    protected array $validationAttributes = [
        'code'           => 'Code',
        'name'           => 'Name',
        'parent_id'      => 'Selection',
        'first_name'     => 'First Name',
        'middle_name'    => 'Middle Name',
        'last_name'      => 'Last Name',
        'contact_number' => 'Contact Number',
        'email'          => 'Email Address',
    ];

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->search = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSectionGradeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAdviserDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetFormFields();
        $this->resetValidation();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['itemBeingDeleted', 'deleteType']);
    }

    private function resetFormFields(?int $keepParentId = null): void
    {
        $this->reset([
            'editingId',
            'code',
            'name',
            'parent_id',
            'first_name',
            'middle_name',
            'last_name',
            'contact_number',
            'email',
        ]);
        $this->parent_id = $keepParentId;
    }

    private function toTitle(string $value): string
    {
        return mb_convert_case(trim($value), MB_CASE_TITLE, 'UTF-8');
    }

    private function toCode(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    public function openCreate(?string $type = null, ?int $parentId = null): void
    {
        try {
            $type ??= $this->activeTab;

            if ($type === 'sections') {
                $firstGl = GradeLevel::orderBy('name')->first();
                if (! $firstGl) {
                    $this->dispatch('toast', message: 'Please create at least one Grade Level before adding sections.', type: 'error');
                    return;
                }
                $parentId ??= $firstGl->id;
            }

            if ($type === 'advisers') {
                $firstDept = Department::orderBy('name')->first();
                if (! $firstDept) {
                    $this->dispatch('toast', message: 'Please create at least one Department before adding advisers.', type: 'error');
                    return;
                }
                $parentId ??= $firstDept->id;
            }

            $this->resetValidation();
            $this->resetFormFields($parentId);
            $this->modalType = $type;
            $this->showModal = true;
        } catch (Exception $e) {
            Log::error('Error opening create modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could not open the form.', type: 'error');
        }
    }

    public function openEdit(string $type, int $id): void
    {
        try {
            $this->resetValidation();
            $this->resetFormFields();
            $this->modalType = $type;
            $this->editingId = $id;

            match ($type) {
                'grade_levels' => (function () use ($id) {
                    $gl = GradeLevel::findOrFail($id);
                    $this->code = $gl->code;
                    $this->name = $gl->name;
                })(),
                'sections' => (function () use ($id) {
                    $sec = Section::findOrFail($id);
                    $this->parent_id = $sec->grade_level_id;
                    $this->name = $sec->name;
                })(),
                'departments' => (function () use ($id) {
                    $dept = Department::findOrFail($id);
                    $this->code = $dept->code;
                    $this->name = $dept->name;
                })(),
                'strands' => (function () use ($id) {
                    $strand = Strand::findOrFail($id);
                    $this->code = $strand->code;
                    $this->name = $strand->name;
                })(),
                'advisers' => (function () use ($id) {
                    $adv = Adviser::findOrFail($id);
                    $this->parent_id = $adv->department_id;
                    $this->first_name = $adv->first_name;
                    $this->middle_name = $adv->middle_name;
                    $this->last_name = $adv->last_name;
                    $this->contact_number = $adv->contact_number;
                    $this->email = $adv->email;
                })(),
            };

            $this->showModal = true;
        } catch (Exception $e) {
            Log::error('Error loading record for edit: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could not load the selected record.', type: 'error');
        }
    }

    protected function rulesForCurrentModal(): array
    {
        return match ($this->modalType) {
            'grade_levels' => [
                'code' => ['required', 'string', 'max:20', Rule::unique('grade_levels', 'code')->ignore($this->editingId)],
                'name' => ['required', 'string', 'max:50', Rule::unique('grade_levels', 'name')->ignore($this->editingId)],
            ],
            'sections' => [
                'parent_id' => ['required', 'exists:grade_levels,id'],
                'name'      => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('sections', 'name')
                        ->where('grade_level_id', $this->parent_id)
                        ->ignore($this->editingId),
                ],
            ],
            'departments' => [
                'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($this->editingId)],
                'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($this->editingId)],
            ],
            'strands' => [
                'code' => ['required', 'string', 'max:30', Rule::unique('strands', 'code')->ignore($this->editingId)],
                'name' => ['required', 'string', 'max:120', Rule::unique('strands', 'name')->ignore($this->editingId)],
            ],
            'advisers' => [
                'parent_id'  => ['required', 'exists:departments,id'],
                'first_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('advisers', 'first_name')
                        ->where('department_id', $this->parent_id)
                        ->where('middle_name', $this->middle_name)
                        ->where('last_name', $this->last_name)
                        ->ignore($this->editingId),
                ],
                'middle_name'    => ['nullable', 'string', 'max:100'],
                'last_name'      => ['required', 'string', 'max:100'],
                'contact_number' => ['nullable', 'digits_between:10,15'],
                'email'          => ['nullable', 'email', 'max:100', Rule::unique('advisers', 'email')->ignore($this->editingId)],
            ],
            default => [],
        };
    }

    public function save(bool $keepOpen = false): void
    {
        $this->cleanFields(['code', 'name', 'first_name', 'middle_name', 'last_name', 'contact_number', 'email']);

        $this->code = $this->code !== '' ? $this->toCode($this->code) : '';
        $this->name = $this->name !== '' ? $this->toTitle($this->name) : '';
        $this->first_name = $this->first_name !== '' ? $this->toTitle($this->first_name) : '';
        $this->middle_name = blank($this->middle_name) ? null : $this->toTitle($this->middle_name);
        $this->last_name = $this->last_name !== '' ? $this->toTitle($this->last_name) : '';
        $this->contact_number = blank($this->contact_number) ? null : trim($this->contact_number);
        $this->email = blank($this->email) ? null : mb_strtolower(trim($this->email), 'UTF-8');

        $validated = $this->validate($this->rulesForCurrentModal(), [], $this->validationAttributes);

        try {
            $isEditing = (bool) $this->editingId;

            match ($this->modalType) {
                'grade_levels' => GradeLevel::updateOrCreate(['id' => $this->editingId], [
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                ]),
                'sections' => Section::updateOrCreate(['id' => $this->editingId], [
                    'grade_level_id' => $validated['parent_id'],
                    'name'           => $validated['name'],
                ]),
                'departments' => Department::updateOrCreate(['id' => $this->editingId], [
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                ]),
                'strands' => Strand::updateOrCreate(['id' => $this->editingId], [
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                ]),
                'advisers' => Adviser::updateOrCreate(['id' => $this->editingId], [
                    'department_id'  => $validated['parent_id'],
                    'first_name'     => $validated['first_name'],
                    'middle_name'    => $validated['middle_name'],
                    'last_name'      => $validated['last_name'],
                    'contact_number' => $validated['contact_number'],
                    'email'          => $validated['email'],
                ]),
            };

            $label = str($this->modalType)->singular()->replace('_', ' ')->title();
            $message = $isEditing ? "{$label} updated successfully." : "{$label} created successfully.";

            if ($keepOpen && ! $isEditing) {
                $keepParent = in_array($this->modalType, ['sections', 'advisers'], true) ? $this->parent_id : null;
                $this->resetFormFields($keepParent);
                $this->resetValidation();
            } else {
                $this->closeModal();
            }

            $this->dispatch('toast', message: $message, type: 'success');
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'name' => 'A duplicate record already exists.',
            ]);
        } catch (Exception $e) {
            Log::error('Error saving academic info record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while saving.', type: 'error');
        }
    }

    public function confirmDelete(string $type, int $id): void
    {
        try {
            if ($type === 'grade_levels') {
                $gl = GradeLevel::withCount('sections')->find($id);
                if ($gl && $gl->sections_count > 0) {
                    $this->dispatch('toast', message: "Cannot delete '{$gl->name}' because it has {$gl->sections_count} assigned sections.", type: 'error');
                    return;
                }
            } elseif ($type === 'departments') {
                $dept = Department::withCount('advisers')->find($id);
                if ($dept && $dept->advisers_count > 0) {
                    $this->dispatch('toast', message: "Cannot delete '{$dept->name}' because it has {$dept->advisers_count} assigned advisers.", type: 'error');
                    return;
                }
            }

            $this->deleteType = $type;
            $this->itemBeingDeleted = $id;
            $this->showDeleteModal = true;
        } catch (Exception $e) {
            Log::error('Error confirming delete: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to proceed with deletion request.', type: 'error');
        }
    }

    public function deleteItem(): void
    {
        if (! $this->itemBeingDeleted || ! $this->deleteType) {
            return;
        }

        try {
            $deleted = match ($this->deleteType) {
                'grade_levels' => (function () {
                    $gl = GradeLevel::withCount('sections')->find($this->itemBeingDeleted);
                    return ($gl && $gl->sections_count === 0) ? (bool) $gl->delete() : false;
                })(),
                'sections'     => (bool) Section::whereKey($this->itemBeingDeleted)->delete(),
                'departments'  => (function () {
                    $dept = Department::withCount('advisers')->find($this->itemBeingDeleted);
                    return ($dept && $dept->advisers_count === 0) ? (bool) $dept->delete() : false;
                })(),
                'strands'      => (bool) Strand::whereKey($this->itemBeingDeleted)->delete(),
                'advisers'     => (bool) Adviser::whereKey($this->itemBeingDeleted)->delete(),
                default        => false,
            };

            if ($deleted) {
                $label = str($this->deleteType)->singular()->replace('_', ' ')->title();
                $this->dispatch('toast', message: "{$label} deleted successfully.", type: 'success');
            } else {
                $this->dispatch('toast', message: 'Cannot delete record with active dependencies.', type: 'error');
            }
        } catch (QueryException $e) {
            Log::error('Query error deleting academic info record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot delete: This record is referenced by active records.', type: 'error');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting academic info record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while deleting.', type: 'error');
        }

        $this->closeDeleteModal();
    }

    #[Layout('components.layouts.app')]
    #[Title('Academic Info')]
    public function render(): View
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $term = trim($this->search);

        $gradeLevels = GradeLevel::withCount('sections')
            ->when($term !== '' && $this->activeTab === 'grade_levels', fn ($q) => $q->where(fn ($sub) => $sub->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%")))
            ->orderBy('name')
            ->paginate(10, ['*'], 'gl_page');

        $sections = Section::with('gradeLevel')
            ->when($term !== '' && $this->activeTab === 'sections', fn ($q) => $q->where('name', $likeOperator, "%{$term}%"))
            ->when($this->sectionGradeFilter !== '', fn ($q) => $q->where('grade_level_id', $this->sectionGradeFilter))
            ->latest()
            ->paginate(10, ['*'], 'sec_page');

        $departments = Department::withCount('advisers')
            ->when($term !== '' && $this->activeTab === 'departments', fn ($q) => $q->where(fn ($sub) => $sub->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%")))
            ->orderBy('name')
            ->paginate(10, ['*'], 'dept_page');

        $strands = Strand::query()
            ->when($term !== '' && $this->activeTab === 'strands', fn ($q) => $q->where(fn ($sub) => $sub->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%")))
            ->orderBy('name')
            ->paginate(10, ['*'], 'strand_page');

        $advisers = Adviser::with('department')
            ->when($term !== '' && $this->activeTab === 'advisers', fn ($q) => $q->where(function ($sub) use ($likeOperator, $term) {
                $sub->where('first_name', $likeOperator, "%{$term}%")
                    ->orWhere('middle_name', $likeOperator, "%{$term}%")
                    ->orWhere('last_name', $likeOperator, "%{$term}%")
                    ->orWhere('email', $likeOperator, "%{$term}%")
                    ->orWhereHas('department', fn ($d) => $d->where('name', $likeOperator, "%{$term}%"));
            }))
            ->when($this->adviserDepartmentFilter !== '', fn ($q) => $q->where('department_id', $this->adviserDepartmentFilter))
            ->latest()
            ->paginate(10, ['*'], 'adv_page');

        $paginator = match ($this->activeTab) {
            'grade_levels' => $gradeLevels,
            'sections'     => $sections,
            'departments'  => $departments,
            'strands'      => $strands,
            'advisers'     => $advisers,
        };

        return view('livewire.academic-info', [
            'rows'           => $paginator,
            'allGradeLevels' => GradeLevel::orderBy('name')->get(['id', 'name']),
            'allDepartments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
