<?php

namespace App\Livewire;

use App\Models\GradeLevel;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Registrations extends Component
{
    use WithPagination;

    // Active Navigation Tab ('users' or 'patrons')
    public string $activeTab = 'users';

    // Global Search Query
    public string $search = '';

    // Modal Control Flags
    public bool $showUserModal = false;
    public bool $showPatronModal = false;
    public bool $showDeleteModal = false;

    // Delete Modal State
    public ?string $deleteType = null; // 'user' or 'patron'
    public ?int $idBeingDeleted = null;

    // ------------------------------------------------------------------
    // USER FORM PROPERTIES
    // ------------------------------------------------------------------
    public ?int $userIdBeingEdited = null;
    public string $u_first_name = '';
    public string $u_middle_name = '';
    public string $u_last_name = '';
    public string $u_suffix = '';
    public string $u_username = '';
    public string $u_role = 'librarian';
    public string $u_email = '';
    public string $u_contact_number = '';
    public string $u_address = '';
    public string $u_password = '';

    // ------------------------------------------------------------------
    // PATRON FORM PROPERTIES
    // ------------------------------------------------------------------
    public ?int $patronIdBeingEdited = null;
    public string $p_patron_id = '';
    public string $p_first_name = '';
    public string $p_middle_name = '';
    public string $p_last_name = '';
    public string $p_suffix = '';
    public ?int $p_patron_type_id = null;
    public ?int $p_grade_level_id = null;
    public ?int $p_section_id = null;
    public string $p_email = '';
    public string $p_contact_number = '';
    public string $p_address = '';
    public string $p_status = 'active';

    // ------------------------------------------------------------------
    // COMPUTED PROPERTIES
    // ------------------------------------------------------------------
    #[Computed]
    public function isEditingAdmin(): bool
    {
        if (! $this->userIdBeingEdited) {
            return false;
        }

        $user = User::find($this->userIdBeingEdited);

        return $user && $user->role === 'admin';
    }

    // Empty String Normalizers for FK Dropdowns
    public function updatedPPatronTypeId($value): void
    {
        if (blank($value)) {
            $this->p_patron_type_id = null;
        }
    }

    public function updatedPGradeLevelId($value): void
    {
        if (blank($value)) {
            $this->p_grade_level_id = null;
        }
        $this->p_section_id = null;
    }

    public function updatedPSectionId($value): void
    {
        if (blank($value)) {
            $this->p_section_id = null;
        }
    }

    // Reset pagination when searching or switching tabs
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

    // ------------------------------------------------------------------
    // USER MODAL ACTIONS
    // ------------------------------------------------------------------
    public function openCreateUserModal(): void
    {
        $this->resetUserForm();
        $this->showUserModal = true;
    }

    public function openEditUserModal(int $id): void
    {
        $this->resetUserForm();
        $user = User::findOrFail($id);

        $this->userIdBeingEdited = $user->id;
        $this->u_first_name = $user->first_name ?? '';
        $this->u_middle_name = $user->middle_name ?? '';
        $this->u_last_name = $user->last_name ?? '';
        $this->u_suffix = $user->suffix ?? '';
        $this->u_username = $user->username;
        $this->u_role = $user->role;
        $this->u_email = $user->email ?? '';
        $this->u_contact_number = $user->contact_number ?? '';
        $this->u_address = $user->address ?? '';

        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        $cleanSuffix = trim($this->u_suffix) ?: null;

        // Composite full-name check matching user schema unique index
        $userFullNameRule = Rule::unique('users', 'first_name')
            ->where('first_name', trim($this->u_first_name))
            ->where('middle_name', trim($this->u_middle_name))
            ->where('last_name', trim($this->u_last_name))
            ->when($cleanSuffix, fn ($q) => $q->where('suffix', $cleanSuffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->userIdBeingEdited);

        $rules = [
            'u_first_name' => ['required', 'string', 'max:50', $userFullNameRule],
            'u_middle_name' => 'required|string|max:50',
            'u_last_name' => 'required|string|max:50',
            'u_suffix' => 'nullable|string|max:10',
            'u_username' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'username')->ignore($this->userIdBeingEdited),
            ],
            'u_role' => 'required|in:admin,librarian,assistant',
            'u_email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('users', 'email')
                    ->ignore($this->userIdBeingEdited)
                    ->when(! trim($this->u_email), fn ($rule) => $rule->whereNull('email')),
            ],
            'u_contact_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'contact_number')
                    ->ignore($this->userIdBeingEdited),
            ],
            'u_address' => 'required|string|max:255',
            'u_password' => $this->userIdBeingEdited ? 'nullable|min:6' : 'required|min:6',
        ];

        $validated = $this->validate($rules, [
            'u_first_name.unique' => 'A user with this identical full name already exists.',
            'u_username.unique' => 'This username is already taken.',
            'u_email.unique' => 'This email address is already registered.',
            'u_contact_number.unique' => 'This contact number is already registered to another user.',
        ]);

        // Security check: Only Admins can set or alter user roles to 'admin'
        $currentUser = Auth::user();
        $assignedRole = $validated['u_role'];

        if ($this->userIdBeingEdited) {
            $existingUser = User::findOrFail($this->userIdBeingEdited);
            if ($currentUser->role !== 'admin' && ($validated['u_role'] === 'admin' || $existingUser->role === 'admin')) {
                $assignedRole = $existingUser->role; // Maintain original role if non-admin attempts edit
            }
        } elseif ($currentUser->role !== 'admin' && $validated['u_role'] === 'admin') {
            $assignedRole = 'librarian'; // Prevent non-admins from creating admin users
        }

        $data = [
            'first_name' => trim($validated['u_first_name']),
            'middle_name' => trim($validated['u_middle_name']),
            'last_name' => trim($validated['u_last_name']),
            'suffix' => $cleanSuffix,
            'username' => trim($validated['u_username']),
            'role' => $assignedRole,
            'email' => trim($validated['u_email']) ?: null,
            'contact_number' => trim($validated['u_contact_number']),
            'address' => trim($validated['u_address']),
        ];

        if (! empty($validated['u_password'])) {
            $data['password'] = Hash::make($validated['u_password']);
        }

        try {
            if ($this->userIdBeingEdited) {
                User::findOrFail($this->userIdBeingEdited)->update($data);
            } else {
                User::create($data);
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'u_username' => 'A database unique constraint error occurred while saving this user.',
            ]);
        }

        $message = $this->userIdBeingEdited ? 'User updated successfully.' : 'User created successfully.';

        $this->showUserModal = false;
        $this->resetUserForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetUserForm(): void
    {
        $this->reset([
            'userIdBeingEdited',
            'u_first_name',
            'u_middle_name',
            'u_last_name',
            'u_suffix',
            'u_username',
            'u_role',
            'u_email',
            'u_contact_number',
            'u_address',
            'u_password',
        ]);
        $this->resetValidation();
    }

    // ------------------------------------------------------------------
    // PATRON MODAL ACTIONS
    // ------------------------------------------------------------------
    public function openCreatePatronModal(): void
    {
        $this->resetPatronForm();

        // Default to first available patron type
        $firstType = PatronType::first();
        if ($firstType) {
            $this->p_patron_type_id = $firstType->id;
        }

        $this->showPatronModal = true;
    }

    public function openEditPatronModal(int $id): void
    {
        $this->resetPatronForm();
        $patron = Patron::findOrFail($id);

        $this->patronIdBeingEdited = $patron->id;
        $this->p_patron_id = $patron->patron_id;
        $this->p_first_name = $patron->first_name;
        $this->p_middle_name = $patron->middle_name;
        $this->p_last_name = $patron->last_name;
        $this->p_suffix = $patron->suffix ?? '';
        $this->p_patron_type_id = $patron->patron_type_id;
        $this->p_grade_level_id = $patron->grade_level_id;
        $this->p_section_id = $patron->section_id;
        $this->p_email = $patron->email ?? '';
        $this->p_contact_number = $patron->contact_number ?? '';
        $this->p_address = $patron->address ?? '';
        $this->p_status = $patron->status;

        $this->showPatronModal = true;
    }

    public function savePatron(): void
    {
        $cleanSuffix = trim($this->p_suffix) ?: null;

        // Dynamic check if selected patron type is student to apply conditional validation
        $selectedType = PatronType::find($this->p_patron_type_id);
        $isStudent = $selectedType && strtolower($selectedType->name) === 'student';

        // Composite full-name check evaluating NULL suffix state correctly
        $fullNameUniqueRule = Rule::unique('patrons', 'first_name')
            ->where('first_name', trim($this->p_first_name))
            ->where('middle_name', trim($this->p_middle_name))
            ->where('last_name', trim($this->p_last_name))
            ->when($cleanSuffix, fn ($q) => $q->where('suffix', $cleanSuffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->patronIdBeingEdited);

        $rules = [
            'p_patron_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('patrons', 'patron_id')->ignore($this->patronIdBeingEdited),
            ],
            'p_first_name' => ['required', 'string', 'max:50', $fullNameUniqueRule],
            'p_middle_name' => 'required|string|max:50',
            'p_last_name' => 'required|string|max:50',
            'p_suffix' => 'nullable|string|max:10',
            'p_patron_type_id' => 'required|exists:patron_types,id',
            'p_grade_level_id' => $isStudent ? 'required|exists:grade_levels,id' : 'nullable|exists:grade_levels,id',
            'p_section_id' => $isStudent ? 'required|exists:sections,id' : 'nullable|exists:sections,id',
            'p_email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('patrons', 'email')->ignore($this->patronIdBeingEdited),
            ],
            'p_contact_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('patrons', 'contact_number')->ignore($this->patronIdBeingEdited),
            ],
            'p_address' => 'required|string|max:255',
            'p_status' => 'required|in:active,inactive,suspended',
        ];

        $this->validate($rules, [
            'p_first_name.unique' => 'A patron with this identical full name already exists in the system.',
            'p_patron_id.unique' => 'This Patron ID is already registered.',
            'p_email.unique' => 'This email is already assigned to another patron.',
            'p_contact_number.unique' => 'This contact number is already assigned to another patron.',
            'p_grade_level_id.required' => 'Grade level is required for student patrons.',
            'p_section_id.required' => 'Section is required for student patrons.',
        ]);

        $payload = [
            'patron_id' => trim($this->p_patron_id),
            'first_name' => trim($this->p_first_name),
            'middle_name' => trim($this->p_middle_name),
            'last_name' => trim($this->p_last_name),
            'suffix' => $cleanSuffix,
            'patron_type_id' => $this->p_patron_type_id,
            'grade_level_id' => $isStudent ? $this->p_grade_level_id : null,
            'section_id' => $isStudent ? $this->p_section_id : null,
            'email' => trim($this->p_email),
            'contact_number' => trim($this->p_contact_number),
            'address' => trim($this->p_address),
            'status' => $this->p_status,
        ];

        try {
            if ($this->patronIdBeingEdited) {
                Patron::findOrFail($this->patronIdBeingEdited)->update($payload);
            } else {
                Patron::create($payload);
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'p_patron_id' => 'A database unique constraint error occurred while saving the patron.',
            ]);
        }

        $message = $this->patronIdBeingEdited ? 'Patron record updated successfully.' : 'Patron record created successfully.';

        $this->showPatronModal = false;
        $this->resetPatronForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetPatronForm(): void
    {
        $this->reset([
            'patronIdBeingEdited',
            'p_patron_id',
            'p_first_name',
            'p_middle_name',
            'p_last_name',
            'p_suffix',
            'p_patron_type_id',
            'p_grade_level_id',
            'p_section_id',
            'p_email',
            'p_contact_number',
            'p_address',
            'p_status',
        ]);
        $this->resetValidation();
    }

    // ------------------------------------------------------------------
    // DELETE ACTIONS
    // ------------------------------------------------------------------
    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->idBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        try {
            if ($this->deleteType === 'user') {
                $user = User::findOrFail($this->idBeingDeleted);

                // Prevent user from deleting themselves
                if ($user->id === Auth::id()) {
                    $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
                    $this->showDeleteModal = false;

                    return;
                }

                if ($user->role !== 'admin') {
                    $user->delete();
                    $this->dispatch('toast', message: 'User account deleted successfully.', type: 'success');
                } else {
                    $this->dispatch('toast', message: 'Admin accounts cannot be deleted.', type: 'error');
                }
            } elseif ($this->deleteType === 'patron') {
                Patron::findOrFail($this->idBeingDeleted)->delete();
                $this->dispatch('toast', message: 'Patron record deleted successfully.', type: 'success');
            }
        } catch (QueryException $e) {
            $this->dispatch('toast', message: 'Cannot delete record: It is referenced by active transactions or logs.', type: 'error');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteType', 'idBeingDeleted']);
    }

    // ------------------------------------------------------------------
    // RENDER
    // ------------------------------------------------------------------
    #[Layout('components.layouts.app')]
    #[Title('Registrations')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // 1. Conditionally fetch Users Data only when active
        $users = $this->activeTab === 'users'
            ? User::query()
                ->when($this->search, function ($query) use ($likeOperator) {
                    $query->where(function ($q) use ($likeOperator) {
                        $q->where('username', $likeOperator, "%{$this->search}%")
                            ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('email', $likeOperator, "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'usersPage')
            : new LengthAwarePaginator([], 0, 10);

        // 2. Conditionally fetch Patrons Data only when active
        $patrons = $this->activeTab === 'patrons'
            ? Patron::with(['patronType', 'gradeLevel', 'section'])
                ->when($this->search, function ($query) use ($likeOperator) {
                    $query->where(function ($q) use ($likeOperator) {
                        $q->where('patron_id', $likeOperator, "%{$this->search}%")
                            ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('email', $likeOperator, "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'patronsPage')
            : new LengthAwarePaginator([], 0, 10);

        // 3. Dropdown Datasets (Lightweight projections)
        $patronTypes = PatronType::orderBy('name')->get(['id', 'name']);
        $allGradeLevels = GradeLevel::orderBy('name')->get(['id', 'name', 'code']);
        $availableSections = $this->p_grade_level_id
            ? Section::where('grade_level_id', $this->p_grade_level_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        // 4. Check if currently selected Patron Type is "Student"
        $selectedPatronType = $patronTypes->firstWhere('id', $this->p_patron_type_id);
        $isStudentType = $selectedPatronType && strtolower($selectedPatronType->name) === 'student';

        return view('livewire.registrations', [
            'users' => $users,
            'patrons' => $patrons,
            'patronTypes' => $patronTypes,
            'allGradeLevels' => $allGradeLevels,
            'availableSections' => $availableSections,
            'isStudentType' => $isStudentType,
        ]);
    }
}
