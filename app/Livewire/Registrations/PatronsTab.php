<?php // app/Livewire/Registrations/PatronsTab.php

namespace App\Livewire\Registrations;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Adviser;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\Section;
use App\Models\Strand;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PatronsTab extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public bool $showPatronModal = false;
    public bool $showDeleteModal = false;
    public ?int $idBeingDeleted = null;

    public ?int $patronIdBeingEdited = null;
    public string $p_school_id = '';
    public string $p_rfid_tag = '';
    public string $p_first_name = '';
    public ?string $p_middle_name = null;
    public string $p_last_name = '';
    public ?string $p_suffix = null;
    public ?int $p_patron_type_id = null;
    public ?int $p_grade_level_id = null;
    public ?int $p_section_id = null;
    public ?int $p_department_id = null;
    public ?int $p_strand_id = null;
    public ?int $p_adviser_id = null;
    public string $p_email = '';
    public string $p_contact_number = '';
    public string $p_address = '';
    public string $p_status = 'active';

    private function toTitle(string $value): string
    {
        return mb_convert_case(trim($value), MB_CASE_TITLE, 'UTF-8');
    }

    private function isSeniorHighDepartment(?int $departmentId): bool
    {
        if (! $departmentId) {
            return false;
        }

        $dept = Department::find($departmentId);
        if (! $dept) {
            return false;
        }

        $deptHaystack = strtolower(trim($dept->name . ' ' . $dept->code));

        return str_contains($deptHaystack, 'senior high') || str_contains($deptHaystack, 'shs');
    }

    private function getAvailableGradeLevels(Collection $allGradeLevels, ?int $departmentId): Collection
    {
        if (! $departmentId) {
            return $allGradeLevels;
        }

        $dept = Department::find($departmentId);
        if (! $dept) {
            return $allGradeLevels;
        }

        $deptHaystack = strtolower(trim($dept->name . ' ' . $dept->code));
        $isShsDept = str_contains($deptHaystack, 'senior high') || str_contains($deptHaystack, 'shs');
        $isJhsDept = str_contains($deptHaystack, 'junior high') || str_contains($deptHaystack, 'jhs');

        if ($isShsDept) {
            $filtered = $allGradeLevels->filter(function ($grade) {
                $g = strtolower($grade->name . ' ' . $grade->code);

                return str_contains($g, '11') || str_contains($g, '12') || str_contains($g, 'senior high') || str_contains($g, 'shs');
            })->values();

            return $filtered->isNotEmpty() ? $filtered : $allGradeLevels;
        }

        if ($isJhsDept) {
            $filtered = $allGradeLevels->filter(function ($grade) {
                $g = strtolower($grade->name . ' ' . $grade->code);

                return str_contains($g, '7')
                    || str_contains($g, '8')
                    || str_contains($g, '9')
                    || str_contains($g, '10')
                    || str_contains($g, 'junior high')
                    || str_contains($g, 'jhs');
            })->values();

            return $filtered->isNotEmpty() ? $filtered : $allGradeLevels;
        }

        return $allGradeLevels;
    }

    private function isSeniorHighSelection(?int $gradeLevelId, ?int $departmentId): bool
    {
        return $this->isSeniorHighDepartment($departmentId);
    }

    private function throwPatronUniqueValidation(UniqueConstraintViolationException $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'patrons_rfid_tag_unique') || str_contains($message, 'rfid_tag')) {
            throw ValidationException::withMessages([
                'p_rfid_tag' => 'This RFID Tag Is Already Assigned To Another Patron.',
            ]);
        }

        if (str_contains($message, 'patrons_school_id_unique') || str_contains($message, 'school_id')) {
            throw ValidationException::withMessages([
                'p_school_id' => 'This Student/Employee # Is Already Assigned To Another Patron.',
            ]);
        }

        if (str_contains($message, 'patrons_email_unique') || str_contains($message, 'email')) {
            throw ValidationException::withMessages([
                'p_email' => 'This Email Address Is Already Assigned To Another Patron.',
            ]);
        }

        if (str_contains($message, 'patrons_contact_number_unique') || str_contains($message, 'contact_number')) {
            throw ValidationException::withMessages([
                'p_contact_number' => 'This Contact Number Is Already Assigned To Another Patron.',
            ]);
        }

        throw ValidationException::withMessages([
            'p_rfid_tag' => 'This RFID Tag Or School ID Is Already Assigned To Another Patron Record.',
        ]);
    }

    public function updatedPPatronTypeId($value): void
    {
        $this->p_patron_type_id = blank($value) ? null : (int) $value;
        $this->p_grade_level_id = null;
        $this->p_section_id = null;
        $this->p_department_id = null;
        $this->p_strand_id = null;
        $this->p_adviser_id = null;
    }

    public function updatedPDepartmentId($value): void
    {
        $this->p_department_id = blank($value) ? null : (int) $value;
        $this->p_grade_level_id = null;
        $this->p_section_id = null;
        $this->p_strand_id = null;
        $this->p_adviser_id = null;
    }

    public function updatedPGradeLevelId($value): void
    {
        $this->p_grade_level_id = blank($value) ? null : (int) $value;
        $this->p_section_id = null;

        if (! $this->isSeniorHighSelection($this->p_grade_level_id, $this->p_department_id)) {
            $this->p_strand_id = null;
        }
    }

    public function updatedPSectionId($value): void
    {
        $this->p_section_id = blank($value) ? null : (int) $value;
    }

    public function updatedPStrandId($value): void
    {
        $this->p_strand_id = blank($value) ? null : (int) $value;
    }

    public function updatedPAdviserId($value): void
    {
        $this->p_adviser_id = blank($value) ? null : (int) $value;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('patronsPage');
    }

    public function openCreatePatronModal(): void
    {
        try {
            $this->resetPatronForm();
            $firstType = PatronType::orderBy('name')->first();
            if ($firstType) {
                $this->p_patron_type_id = $firstType->id;
            }
            $this->showPatronModal = true;
        } catch (Exception $e) {
            Log::error('Error opening create patron modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could Not Open The Patron Form.', type: 'error');
        }
    }

    public function openEditPatronModal(int $id): void
    {
        try {
            $this->resetPatronForm();
            $patron = Patron::findOrFail($id);

            $this->patronIdBeingEdited = $patron->id;
            $this->p_school_id = $patron->school_id;
            $this->p_rfid_tag = $patron->rfid_tag;
            $this->p_first_name = $patron->first_name;
            $this->p_middle_name = $patron->middle_name ?? '';
            $this->p_last_name = $patron->last_name;
            $this->p_suffix = $patron->suffix ?? '';
            $this->p_patron_type_id = $patron->patron_type_id;
            $this->p_grade_level_id = $patron->grade_level_id;
            $this->p_section_id = $patron->section_id;
            $this->p_department_id = $patron->department_id;
            $this->p_strand_id = $patron->strand_id;
            $this->p_adviser_id = $patron->adviser_id;
            $this->p_email = $patron->email ?? '';
            $this->p_contact_number = $patron->contact_number ?? '';
            $this->p_address = $patron->address ?? '';
            $this->p_status = $patron->status;

            $this->showPatronModal = true;
        } catch (Exception $e) {
            Log::error('Error opening edit patron modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could Not Load Patron Details.', type: 'error');
        }
    }

    public function savePatron(): void
    {
        $this->cleanFields([
            'p_school_id', 'p_rfid_tag', 'p_first_name', 'p_middle_name',
            'p_last_name', 'p_suffix', 'p_email', 'p_contact_number', 'p_address',
        ]);

        $selectedType = PatronType::find($this->p_patron_type_id);
        $isStudent = $selectedType && strtolower($selectedType->name) === 'student';
        $isSeniorHigh = $isStudent && $this->isSeniorHighSelection($this->p_grade_level_id, $this->p_department_id);

        $gradeLevelId = $isStudent ? $this->p_grade_level_id : null;
        $sectionId = $isStudent ? $this->p_section_id : null;
        $strandId = $isSeniorHigh ? $this->p_strand_id : null;
        $adviserId = $isStudent ? $this->p_adviser_id : null;

        $this->p_grade_level_id = $gradeLevelId;
        $this->p_section_id = $sectionId;
        $this->p_strand_id = $strandId;
        $this->p_adviser_id = $adviserId;

        $firstName = $this->p_first_name !== '' ? $this->toTitle($this->p_first_name) : '';
        $middleName = blank($this->p_middle_name) ? null : $this->toTitle($this->p_middle_name);
        $lastName = $this->p_last_name !== '' ? $this->toTitle($this->p_last_name) : '';
        $suffix = blank($this->p_suffix) ? null : trim($this->p_suffix);
        $schoolId = strtoupper(trim($this->p_school_id));
        $rfidTag = strtoupper(trim($this->p_rfid_tag));
        $email = strtolower(trim($this->p_email));
        $contactNumber = trim($this->p_contact_number);
        $address = trim($this->p_address);

        $fullNameUniqueRule = Rule::unique('patrons', 'first_name')
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->when($middleName, fn ($q) => $q->where('middle_name', $middleName), fn ($q) => $q->whereNull('middle_name'))
            ->when($suffix, fn ($q) => $q->where('suffix', $suffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->patronIdBeingEdited);

        $rules = [
            'p_school_id'      => ['required', 'string', 'max:255', Rule::unique('patrons', 'school_id')->ignore($this->patronIdBeingEdited)],
            'p_rfid_tag'       => ['required', 'string', 'max:255', Rule::unique('patrons', 'rfid_tag')->ignore($this->patronIdBeingEdited)],
            'p_first_name'     => ['required', 'string', 'max:50', $fullNameUniqueRule],
            'p_middle_name'    => ['nullable', 'string', 'max:50'],
            'p_last_name'      => ['required', 'string', 'max:50'],
            'p_suffix'         => ['nullable', 'string', 'max:10'],
            'p_patron_type_id' => ['required', 'exists:patron_types,id'],
            'p_department_id'  => ['required', 'exists:departments,id'],
            'p_grade_level_id' => [$isStudent ? 'required' : 'nullable', 'exists:grade_levels,id'],
            'p_section_id'     => [$isStudent ? 'required' : 'nullable', 'exists:sections,id'],
            'p_strand_id'      => [$isSeniorHigh ? 'required' : 'nullable', 'exists:strands,id'],
            'p_adviser_id'     => [$isStudent ? 'required' : 'nullable', 'exists:advisers,id'],
            'p_email'          => ['required', 'email', 'max:100', Rule::unique('patrons', 'email')->ignore($this->patronIdBeingEdited)],
            'p_contact_number' => ['required', 'string', 'max:20', Rule::unique('patrons', 'contact_number')->ignore($this->patronIdBeingEdited)],
            'p_address'        => ['required', 'string', 'max:255'],
            'p_status'         => ['required', 'in:active,inactive,suspended'],
        ];

        $attributes = [
            'p_first_name'     => 'First Name',
            'p_middle_name'    => 'Middle Name',
            'p_last_name'      => 'Last Name',
            'p_suffix'         => 'Suffix',
            'p_school_id'      => 'Student/Employee #',
            'p_rfid_tag'       => 'RFID Tag',
            'p_patron_type_id' => 'Patron Type',
            'p_grade_level_id' => 'Grade Level',
            'p_section_id'     => 'Section',
            'p_department_id'  => 'Department',
            'p_strand_id'      => 'Strand',
            'p_adviser_id'     => 'Adviser',
            'p_email'          => 'Email Address',
            'p_contact_number' => 'Contact Number',
            'p_address'        => 'Address',
            'p_status'         => 'Status',
        ];

        $this->validate($rules, [], $attributes);

        $payload = [
            'school_id'      => $schoolId,
            'rfid_tag'       => $rfidTag,
            'first_name'     => $firstName,
            'middle_name'    => $middleName,
            'last_name'      => $lastName,
            'suffix'         => $suffix,
            'patron_type_id' => $this->p_patron_type_id,
            'department_id'  => $this->p_department_id,
            'grade_level_id' => $gradeLevelId,
            'section_id'     => $sectionId,
            'strand_id'      => $strandId,
            'adviser_id'     => $adviserId,
            'email'          => $email,
            'contact_number' => $contactNumber,
            'address'        => $address,
            'status'         => $this->p_status,
        ];

        try {
            DB::transaction(function () use ($payload): void {
                if ($this->patronIdBeingEdited) {
                    $patron = Patron::query()->lockForUpdate()->findOrFail($this->patronIdBeingEdited);
                    $patron->update($payload);
                } else {
                    Patron::query()->create($payload);
                }
            });
        } catch (UniqueConstraintViolationException $e) {
            $this->throwPatronUniqueValidation($e);
        } catch (QueryException $e) {
            Log::error('Query error saving patron: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Database Error Saving Patron. Check Required Fields Or Duplicate Values.', type: 'error');
            return;
        } catch (Exception $e) {
            Log::error('Error saving patron: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Unexpected Error Occurred While Saving The Patron.', type: 'error');
            return;
        }

        $message = $this->patronIdBeingEdited ? 'Patron Record Updated Successfully.' : 'Patron Record Created Successfully.';

        $this->showPatronModal = false;
        $this->resetPatronForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetPatronForm(): void
    {
        $this->reset([
            'patronIdBeingEdited', 'p_school_id', 'p_rfid_tag', 'p_first_name',
            'p_middle_name', 'p_last_name', 'p_suffix', 'p_patron_type_id',
            'p_grade_level_id', 'p_section_id', 'p_department_id', 'p_strand_id',
            'p_adviser_id', 'p_email', 'p_contact_number', 'p_address',
        ]);
        $this->p_status = 'active';
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        $this->idBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        try {
            DB::transaction(function (): void {
                $patron = Patron::query()->lockForUpdate()->findOrFail($this->idBeingDeleted);
                $patron->delete();
            });

            $this->dispatch('toast', message: 'Patron Record Deleted Successfully.', type: 'success');
        } catch (QueryException $e) {
            Log::error('Query error deleting patron: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot Delete Record: It Is Referenced By Active Transactions Or Patron Logs.', type: 'error');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting patron: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Unexpected Error Occurred While Deleting The Record.', type: 'error');
        }

        $this->showDeleteModal = false;
        $this->reset(['idBeingDeleted']);
    }

    public function render()
    {
        try {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $term = trim($this->search);

            $patrons = Patron::with(['patronType', 'gradeLevel', 'section', 'department', 'strand', 'adviser'])
                ->when($term !== '', function ($query) use ($operator, $term) {
                    $query->where(function ($q) use ($operator, $term) {
                        $q->where('school_id', $operator, "%{$term}%")
                            ->orWhere('rfid_tag', $operator, "%{$term}%")
                            ->orWhere('first_name', $operator, "%{$term}%")
                            ->orWhere('middle_name', $operator, "%{$term}%")
                            ->orWhere('last_name', $operator, "%{$term}%")
                            ->orWhere('email', $operator, "%{$term}%")
                            ->orWhere('address', $operator, "%{$term}%")
                            ->orWhereHas('gradeLevel', fn ($g) => $g->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                            ->orWhereHas('section', fn ($s) => $s->where('name', $operator, "%{$term}%"))
                            ->orWhereHas('department', fn ($d) => $d->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                            ->orWhereHas('strand', fn ($st) => $st->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                            ->orWhereHas('adviser', fn ($a) => $a->where('first_name', $operator, "%{$term}%")->orWhere('last_name', $operator, "%{$term}%"));
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'patronsPage');

            $patronTypes = PatronType::orderBy('name')->get(['id', 'name']);
            $allGradeLevels = GradeLevel::orderBy('name')->get(['id', 'name', 'code']);
            $availableGradeLevels = $this->getAvailableGradeLevels($allGradeLevels, $this->p_department_id);
            $allDepartments = Department::orderBy('name')->get(['id', 'name', 'code']);
            $allStrands = Strand::orderBy('name')->get(['id', 'name', 'code']);

            $availableSections = $this->p_grade_level_id
                ? Section::where('grade_level_id', $this->p_grade_level_id)->orderBy('name')->get(['id', 'name'])
                : collect();

            $availableAdvisers = $this->p_department_id
                ? Adviser::where('department_id', $this->p_department_id)->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name'])
                : collect();

            $selectedPatronType = $patronTypes->firstWhere('id', $this->p_patron_type_id);
            $isStudentType = $selectedPatronType && strtolower($selectedPatronType->name) === 'student';
            $isSeniorHighType = $isStudentType && $this->isSeniorHighSelection($this->p_grade_level_id, $this->p_department_id);
        } catch (Exception $e) {
            Log::error('Error rendering patrons tab: ' . $e->getMessage());
            $patrons = new LengthAwarePaginator([], 0, 10);
            $patronTypes = collect();
            $availableGradeLevels = collect();
            $allDepartments = collect();
            $allStrands = collect();
            $availableSections = collect();
            $availableAdvisers = collect();
            $isStudentType = false;
            $isSeniorHighType = false;
        }

        return view('livewire.registrations.patrons-tab', [
            'patrons'              => $patrons,
            'patronTypes'          => $patronTypes,
            'availableGradeLevels' => $availableGradeLevels,
            'allDepartments'       => $allDepartments,
            'allStrands'           => $allStrands,
            'availableSections'    => $availableSections,
            'availableAdvisers'    => $availableAdvisers,
            'isStudentType'        => $isStudentType,
            'isSeniorHighType'     => $isSeniorHighType,
        ]);
    }
}
