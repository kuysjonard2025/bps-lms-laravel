<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Adviser;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\Section;
use App\Models\Strand;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Registrations extends Component
{
    use WithPagination, SanitizesInputs;

    // 'users' | 'patrons'
    public string $activeTab = 'users';
    public string $search = '';

    public bool $showUserModal = false;
    public bool $showPatronModal = false;
    public bool $showDeleteModal = false;

    public ?string $deleteType = null;
    public ?int $idBeingDeleted = null;

    // User Form Properties
    public ?int $userIdBeingEdited = null;
    public string $u_first_name = '';
    public ?string $u_middle_name = null;
    public string $u_last_name = '';
    public ?string $u_suffix = null;
    public string $u_username = '';
    public string $u_role = 'assistant';
    public string $u_email = '';
    public string $u_contact_number = '';
    public string $u_address = '';
    public string $u_password = '';

    // Patron Form Properties
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

    protected array $validationAttributes = [
        'u_first_name'     => 'First Name',
        'u_middle_name'    => 'Middle Name',
        'u_last_name'      => 'Last Name',
        'u_suffix'         => 'Suffix',
        'u_username'       => 'Username',
        'u_role'           => 'Role',
        'u_email'          => 'Email Address',
        'u_contact_number' => 'Contact Number',
        'u_address'        => 'Address',
        'u_password'       => 'Password',
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

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

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

    /**
     * Filter Grade Levels based on the selected Department (e.g. SHS -> Grades 11-12, JHS -> Grades 7-10).
     */
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

    private function getAvailableStrands(): Collection
    {
        return Strand::orderBy('name')->get(['id', 'name', 'code']);
    }

    /**
     * Strands apply strictly when the selected Department is SHS (Senior High School).
     */
    private function isSeniorHighSelection(?int $gradeLevelId, ?int $departmentId): bool
    {
        return $this->isSeniorHighDepartment($departmentId);
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
        $this->resetPage('usersPage');
        $this->resetPage('patronsPage');
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage('usersPage');
        $this->resetPage('patronsPage');
        $this->search = '';
    }

    public function openCreateUserModal(): void
    {
        try {
            $this->resetUserForm();
            $this->u_role = 'assistant';
            $this->showUserModal = true;
        } catch (Exception $e) {
            Log::error('Error opening create user modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could Not Open The User Form.', type: 'error');
        }
    }

    public function openEditUserModal(int $id): void
    {
        try {
            $this->resetUserForm();
            $user = User::findOrFail($id);

            $this->userIdBeingEdited = $user->id;
            $this->u_first_name = $user->first_name ?? '';
            $this->u_middle_name = $user->middle_name ?? '';
            $this->u_last_name = $user->last_name ?? '';
            $this->u_suffix = $user->suffix ?? '';
            $this->u_username = $user->username;
            $this->u_role = $user->role ?? 'assistant';
            $this->u_email = $user->email ?? '';
            $this->u_contact_number = $user->contact_number ?? '';
            $this->u_address = $user->address ?? '';

            $this->showUserModal = true;
        } catch (Exception $e) {
            Log::error('Error opening edit user modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could Not Load User Details.', type: 'error');
        }
    }

    public function saveUser(): void
    {
        $fields = [
            'u_first_name', 'u_middle_name', 'u_last_name', 'u_suffix',
            'u_username', 'u_email', 'u_contact_number', 'u_address',
        ];

        $this->cleanFields($fields);

        $firstName = $this->u_first_name !== '' ? $this->toTitle($this->u_first_name) : '';
        $middleName = blank($this->u_middle_name) ? null : $this->toTitle($this->u_middle_name);
        $lastName = $this->u_last_name !== '' ? $this->toTitle($this->u_last_name) : '';
        $suffix = blank($this->u_suffix) ? null : trim($this->u_suffix);
        $username = strtolower(trim($this->u_username));
        $email = strtolower(trim($this->u_email));
        $contactNumber = trim($this->u_contact_number);
        $address = trim($this->u_address);

        $userBeingEdited = $this->userIdBeingEdited ? User::find($this->userIdBeingEdited) : null;
        $isLibrarian = $userBeingEdited && $userBeingEdited->role === 'librarian';

        $userFullNameRule = Rule::unique('users', 'first_name')
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->when($middleName, fn ($q) => $q->where('middle_name', $middleName), fn ($q) => $q->whereNull('middle_name'))
            ->when($suffix, fn ($q) => $q->where('suffix', $suffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->userIdBeingEdited);

        $rules = [
            'u_first_name'     => ['required', 'string', 'max:50', $userFullNameRule],
            'u_middle_name'    => ['nullable', 'string', 'max:50'],
            'u_last_name'      => ['required', 'string', 'max:50'],
            'u_suffix'         => ['nullable', 'string', 'max:10'],
            'u_username'       => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('users', 'username')->ignore($this->userIdBeingEdited)],
            'u_role'           => ['required', $isLibrarian ? 'in:librarian' : 'in:assistant'],
            'u_email'          => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($this->userIdBeingEdited)],
            'u_contact_number' => ['required', 'string', 'max:20', Rule::unique('users', 'contact_number')->ignore($this->userIdBeingEdited)],
            'u_address'        => ['required', 'string', 'max:255'],
            'u_password'       => [$this->userIdBeingEdited ? 'nullable' : 'required', 'min:6'],
        ];

        $validated = $this->validate($rules, [], $this->validationAttributes);

        $role = $isLibrarian ? 'librarian' : 'assistant';
        $data = [
            'first_name'     => $firstName,
            'middle_name'    => $middleName,
            'last_name'      => $lastName,
            'suffix'         => $suffix,
            'username'       => $username,
            'role'           => $role,
            'email'          => $email,
            'contact_number' => $contactNumber,
            'address'        => $address,
        ];

        if (! empty($validated['u_password'])) {
            $data['password'] = Hash::make($validated['u_password']);
        }

        try {
            if ($this->userIdBeingEdited) {
                User::findOrFail($this->userIdBeingEdited)->update($data);
                $message = Str::headline($role) . ' User Updated Successfully.';
            } else {
                $user = User::create($data);
                $user->sendEmailVerificationNotification();
                $message = Str::headline($role) . ' User Created Successfully. Verification Email Dispatched.';
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'u_username' => 'A Database Unique Constraint Error Occurred While Saving This User.',
            ]);
        } catch (Exception $e) {
            Log::error('Error saving user: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Unexpected Error Occurred While Saving The User.', type: 'error');
            return;
        }

        $this->showUserModal = false;
        $this->resetUserForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetUserForm(): void
    {
        $this->reset([
            'userIdBeingEdited', 'u_first_name', 'u_middle_name', 'u_last_name',
            'u_suffix', 'u_username', 'u_email', 'u_contact_number', 'u_address', 'u_password',
        ]);
        $this->u_role = 'assistant';
        $this->resetValidation();
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
        $fields = [
            'p_school_id', 'p_rfid_tag', 'p_first_name', 'p_middle_name',
            'p_last_name', 'p_suffix', 'p_email', 'p_contact_number', 'p_address',
        ];

        $this->cleanFields($fields);

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

        $this->validate($rules, [], $this->validationAttributes);

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
            if ($this->patronIdBeingEdited) {
                Patron::findOrFail($this->patronIdBeingEdited)->update($payload);
            } else {
                Patron::create($payload);
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'p_rfid_tag' => 'This RFID Tag Or School ID Is Already Assigned To Another Patron Record.',
            ]);
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

    public function confirmDelete(string $type, int $id): void
    {
        try {
            $this->deleteType = $type;
            $this->idBeingDeleted = $id;
            $this->showDeleteModal = true;
        } catch (Exception $e) {
            Log::error('Error confirming delete: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable To Proceed With Deletion Request.', type: 'error');
        }
    }

    public function deleteRecord(): void
    {
        try {
            if ($this->deleteType === 'user') {
                $user = User::findOrFail($this->idBeingDeleted);

                if ($user->id === Auth::id()) {
                    $this->dispatch('toast', message: 'You Cannot Delete Your Own Account.', type: 'error');
                    $this->showDeleteModal = false;
                    return;
                }

                if ($user->role !== 'admin' && $user->role !== 'librarian') {
                    $user->delete();
                    $this->dispatch('toast', message: 'User Account Deleted Successfully.', type: 'success');
                } else {
                    $this->dispatch('toast', message: 'Admin Or Librarian Accounts Cannot Be Deleted From This View.', type: 'error');
                }
            } elseif ($this->deleteType === 'patron') {
                Patron::findOrFail($this->idBeingDeleted)->delete();
                $this->dispatch('toast', message: 'Patron Record Deleted Successfully.', type: 'success');
            }
        } catch (QueryException $e) {
            Log::error('Query error deleting record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot Delete Record: It Is Referenced By Active Transactions Or Patron Logs.', type: 'error');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An Unexpected Error Occurred While Deleting The Record.', type: 'error');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteType', 'idBeingDeleted']);
    }

    #[Layout('components.layouts.app')]
    #[Title('Registrations')]
    public function render()
    {
        try {
            $likeOperator = $this->likeOperator();
            $term = trim($this->search);

            $users = $this->activeTab === 'users'
                ? User::query()
                    ->when($term !== '', function ($query) use ($likeOperator, $term) {
                        $query->where(function ($q) use ($likeOperator, $term) {
                            $q->where('username', $likeOperator, "%{$term}%")
                                ->orWhere('first_name', $likeOperator, "%{$term}%")
                                ->orWhere('middle_name', $likeOperator, "%{$term}%")
                                ->orWhere('last_name', $likeOperator, "%{$term}%")
                                ->orWhere('email', $likeOperator, "%{$term}%")
                                ->orWhere('address', $likeOperator, "%{$term}%");
                        });
                    })
                    ->latest()
                    ->paginate(10, ['*'], 'usersPage')
                : new LengthAwarePaginator([], 0, 10);

            $patrons = $this->activeTab === 'patrons'
                ? Patron::with(['patronType', 'gradeLevel', 'section', 'department', 'strand', 'adviser'])
                    ->when($term !== '', function ($query) use ($likeOperator, $term) {
                        $query->where(function ($q) use ($likeOperator, $term) {
                            $q->where('school_id', $likeOperator, "%{$term}%")
                                ->orWhere('rfid_tag', $likeOperator, "%{$term}%")
                                ->orWhere('first_name', $likeOperator, "%{$term}%")
                                ->orWhere('middle_name', $likeOperator, "%{$term}%")
                                ->orWhere('last_name', $likeOperator, "%{$term}%")
                                ->orWhere('email', $likeOperator, "%{$term}%")
                                ->orWhere('address', $likeOperator, "%{$term}%")
                                ->orWhereHas('gradeLevel', fn ($g) => $g->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%"))
                                ->orWhereHas('section', fn ($s) => $s->where('name', $likeOperator, "%{$term}%"))
                                ->orWhereHas('department', fn ($d) => $d->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%"))
                                ->orWhereHas('strand', fn ($st) => $st->where('name', $likeOperator, "%{$term}%")->orWhere('code', $likeOperator, "%{$term}%"))
                                ->orWhereHas('adviser', fn ($a) => $a->where('first_name', $likeOperator, "%{$term}%")->orWhere('last_name', $likeOperator, "%{$term}%"));
                        });
                    })
                    ->latest()
                    ->paginate(10, ['*'], 'patronsPage')
                : new LengthAwarePaginator([], 0, 10);

            $patronTypes = PatronType::orderBy('name')->get(['id', 'name']);
            $allGradeLevels = GradeLevel::orderBy('name')->get(['id', 'name', 'code']);
            $availableGradeLevels = $this->getAvailableGradeLevels($allGradeLevels, $this->p_department_id);
            $allDepartments = Department::orderBy('name')->get(['id', 'name', 'code']);
            $allStrands = $this->getAvailableStrands();

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
            Log::error('Error rendering registrations view: ' . $e->getMessage());
            $users = new LengthAwarePaginator([], 0, 10);
            $patrons = new LengthAwarePaginator([], 0, 10);
            $patronTypes = collect();
            $allGradeLevels = collect();
            $availableGradeLevels = collect();
            $allDepartments = collect();
            $allStrands = collect();
            $availableSections = collect();
            $availableAdvisers = collect();
            $isStudentType = false;
            $isSeniorHighType = false;
        }

        return view('livewire.registrations', [
            'users'                => $users,
            'patrons'              => $patrons,
            'patronTypes'          => $patronTypes,
            'allGradeLevels'       => $allGradeLevels,
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
