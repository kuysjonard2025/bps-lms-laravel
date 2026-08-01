<?php

namespace App\Livewire;

use App\Models\GradeLevel;
use App\Models\Section;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicInfo extends Component
{
    use WithPagination;

    // Active Tab: 'grade_levels' or 'sections'
    public string $activeTab = 'grade_levels';

    public string $search = '';

    // Modals
    public bool $showGradeLevelModal = false;
    public bool $showSectionModal = false;
    public bool $showDeleteModal = false;

    // Selection/Editing state
    public ?int $gradeLevelIdBeingEdited = null;
    public ?int $sectionIdBeingEdited = null;
    public ?int $itemBeingDeleted = null;
    public string $deleteType = ''; // 'grade_level' or 'section'

    // Grade Level Form Fields
    public string $gl_name = '';
    public string $gl_code = '';

    // Section Form Fields
    public ?int $sec_grade_level_id = null;
    public string $sec_name = '';

    // Filter Sections by Grade Level
    public string $sectionGradeFilter = '';

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

    // --- GRADE LEVEL ACTIONS ---

    public function openCreateGradeLevelModal(): void
    {
        $this->resetValidation();
        $this->reset(['gl_name', 'gl_code', 'gradeLevelIdBeingEdited']);
        $this->showGradeLevelModal = true;
    }

    public function openEditGradeLevelModal(GradeLevel $gradeLevel): void
    {
        $this->resetValidation();
        $this->gradeLevelIdBeingEdited = $gradeLevel->id;
        $this->gl_name = $gradeLevel->name;
        $this->gl_code = $gradeLevel->code;
        $this->showGradeLevelModal = true;
    }

    public function saveGradeLevel(): void
    {
        $rules = [
            'gl_name' => 'required|string|max:100',
            'gl_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('grade_levels', 'code')->ignore($this->gradeLevelIdBeingEdited),
            ],
        ];

        $this->validate($rules, [], [
            'gl_name' => 'grade level name',
            'gl_code' => 'grade level code',
        ]);

        if ($this->gradeLevelIdBeingEdited) {
            GradeLevel::findOrFail($this->gradeLevelIdBeingEdited)->update([
                'name' => trim($this->gl_name),
                'code' => strtoupper(trim($this->gl_code)),
            ]);
            $message = 'Grade level updated successfully.';
        } else {
            GradeLevel::create([
                'name' => trim($this->gl_name),
                'code' => strtoupper(trim($this->gl_code)),
            ]);
            $message = 'Grade level created successfully.';
        }

        $this->showGradeLevelModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // --- SECTION ACTIONS ---

    public function openCreateSectionModal(?int $gradeLevelId = null): void
    {
        $this->resetValidation();
        $this->reset(['sec_grade_level_id', 'sec_name', 'sectionIdBeingEdited']);
        $this->sec_grade_level_id = $gradeLevelId ?? GradeLevel::first()?->id;
        $this->showSectionModal = true;
    }

    public function openEditSectionModal(Section $section): void
    {
        $this->resetValidation();
        $this->sectionIdBeingEdited = $section->id;
        $this->sec_grade_level_id = $section->grade_level_id;
        $this->sec_name = $section->name;
        $this->showSectionModal = true;
    }

    public function saveSection(): void
    {
        $rules = [
            'sec_grade_level_id' => 'required|exists:grade_levels,id',
            'sec_name'           => 'required|string|max:100',
        ];

        $this->validate($rules, [], [
            'sec_grade_level_id' => 'grade level',
            'sec_name'           => 'section name',
        ]);

        if ($this->sectionIdBeingEdited) {
            Section::findOrFail($this->sectionIdBeingEdited)->update([
                'grade_level_id' => $this->sec_grade_level_id,
                'name'           => trim($this->sec_name),
            ]);
            $message = 'Section updated successfully.';
        } else {
            Section::create([
                'grade_level_id' => $this->sec_grade_level_id,
                'name'           => trim($this->sec_name),
            ]);
            $message = 'Section created successfully.';
        }

        $this->showSectionModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // --- DELETE HANDLERS ---

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->itemBeingDeleted = $id;

        if ($type === 'grade_level') {
            $gl = GradeLevel::withCount('sections')->find($id);
            if ($gl && $gl->sections_count > 0) {
                $this->dispatch('toast', message: "Cannot delete '{$gl->name}' because it has {$gl->sections_count} assigned sections.", type: 'error');
                return;
            }
        }

        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if ($this->itemBeingDeleted && $this->deleteType) {
            if ($this->deleteType === 'grade_level') {
                $gl = GradeLevel::find($this->itemBeingDeleted);
                if ($gl && $gl->sections()->count() === 0) {
                    $gl->delete();
                    $this->dispatch('toast', message: 'Grade level deleted successfully.', type: 'success');
                }
            } elseif ($this->deleteType === 'section') {
                Section::destroy($this->itemBeingDeleted);
                $this->dispatch('toast', message: 'Section deleted successfully.', type: 'success');
            }
        }

        $this->showDeleteModal = false;
        $this->itemBeingDeleted = null;
        $this->deleteType = '';
    }

    #[Layout('components.layouts.app')]
    #[Title('Academic Info')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $gradeLevels = GradeLevel::withCount('sections')
            ->when($this->search && $this->activeTab === 'grade_levels', function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('name', $likeOperator, "%{$this->search}%")
                      ->orWhere('code', $likeOperator, "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'gl_page');

        $sections = Section::with('gradeLevel')
            ->when($this->search && $this->activeTab === 'sections', function ($query) use ($likeOperator) {
                $query->where('name', $likeOperator, "%{$this->search}%");
            })
            ->when($this->sectionGradeFilter, fn ($q) => $q->where('grade_level_id', $this->sectionGradeFilter))
            ->latest()
            ->paginate(10, ['*'], 'sec_page');

        return view('livewire.academic-info', [
            'gradeLevels'    => $gradeLevels,
            'allGradeLevels' => GradeLevel::orderBy('name')->get(),
            'sections'       => $sections,
        ]);
    }
}
