<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;

class Registrations extends Component
{
    use WithPagination, SanitizesInputs;

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
    public string $u_middle_name = '';
    public string $u_last_name = '';
    public string $u_suffix = '';
    public string $u_username = '';
    public string $u_role = 'assistant';
    public string $u_email = '';
    public string $u_contact_number = '';
    public string $u_address = '';
    public string $u_password = '';

    // Borrower Form Properties
    public ?int $patronIdBeingEdited = null;
    public string $p_school_id = '';
    public string $p_rfid_tag = '';
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
            $this->dispatch('toast', message: 'Could not open the user form.', type: 'error');
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
            $this->dispatch('toast', message: 'Could not load user details.', type: 'error');
        }
    }

    public function saveUser(): void
    {
        $fields = [
            'u_first_name', 'u_middle_name', 'u_last_name', 'u_suffix',
            'u_username', 'u_email', 'u_contact_number', 'u_address',
        ];

        $this->cleanFields($fields);

        $userBeingEdited = $this->userIdBeingEdited ? User::find($this->userIdBeingEdited) : null;
        $isLibrarian = $userBeingEdited && $userBeingEdited->role === 'librarian';

        $userFullNameRule = Rule::unique('users', 'first_name')
            ->where('first_name', $this->u_first_name)
            ->where('middle_name', $this->u_middle_name)
            ->where('last_name', $this->u_last_name)
            ->when($this->u_suffix, fn ($q) => $q->where('suffix', $this->u_suffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->userIdBeingEdited);

        $rules = [
            'u_first_name' => ['required', 'string', 'max:50', $userFullNameRule],
            'u_middle_name' => 'required|string|max:50',
            'u_last_name' => 'required|string|max:50',
            'u_suffix' => 'nullable|string|max:10',
            'u_username' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($this->userIdBeingEdited),
            ],
            'u_role' => $isLibrarian ? 'required|in:librarian' : 'required|in:assistant',
            'u_email' => [
                'required', 'email', 'max:100',
                Rule::unique('users', 'email')->ignore($this->userIdBeingEdited),
            ],
            'u_contact_number' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'contact_number')->ignore($this->userIdBeingEdited),
            ],
            'u_address' => 'required|string|max:255',
            'u_password' => $this->userIdBeingEdited ? 'nullable|min:6' : 'required|min:6',
        ];

        $validated = $this->validate($rules, [], [
            'u_first_name' => 'First name',
            'u_middle_name' => 'Middle name',
            'u_last_name' => 'Last name',
            'u_suffix' => 'Suffix',
            'u_username' => 'Username',
            'u_role' => 'Role',
            'u_email' => 'Email',
            'u_contact_number' => 'Contact number',
            'u_address' => 'Address',
            'u_password' => 'Password',
        ]);

        $role = $isLibrarian ? 'librarian' : 'assistant';
        $data = [
            'first_name' => $this->u_first_name,
            'middle_name' => $this->u_middle_name,
            'last_name' => $this->u_last_name,
            'suffix' => $this->u_suffix,
            'username' => $this->u_username,
            'role' => $role,
            'email' => $this->u_email,
            'contact_number' => $this->u_contact_number,
            'address' => $this->u_address,
        ];

        if (! empty($validated['u_password'])) {
            $data['password'] = Hash::make($validated['u_password']);
        }

        try {
            if ($this->userIdBeingEdited) {
                User::findOrFail($this->userIdBeingEdited)->update($data);
                $message = ucwords($role) . ' user updated successfully.';
            } else {
                $user = User::create($data);
                $user->sendEmailVerificationNotification();
                $message = ucwords($role) . ' user created successfully. Verification email dispatched.';
            }
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'u_username' => 'A database unique constraint error occurred while saving this user.',
            ]);
        } catch (Exception $e) {
            Log::error('Error saving user: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while saving the user.', type: 'error');
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
            $firstType = PatronType::first();
            if ($firstType) {
                $this->p_patron_type_id = $firstType->id;
            }
            $this->showPatronModal = true;
        } catch (Exception $e) {
            Log::error('Error opening create patron modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could not open the borrower form.', type: 'error');
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
        } catch (Exception $e) {
            Log::error('Error opening edit patron modal: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Could not load borrower details.', type: 'error');
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

        $fullNameUniqueRule = Rule::unique('patrons', 'first_name')
            ->where('first_name', $this->p_first_name)
            ->where('middle_name', $this->p_middle_name)
            ->where('last_name', $this->p_last_name)
            ->when($this->p_suffix, fn ($q) => $q->where('suffix', $this->p_suffix), fn ($q) => $q->whereNull('suffix'))
            ->ignore($this->patronIdBeingEdited);

        $rules = [
            'p_school_id' => ['required', 'string', 'max:255', Rule::unique('patrons', 'school_id')->ignore($this->patronIdBeingEdited)],
            'p_rfid_tag' => ['required', 'string', 'max:255', Rule::unique('patrons', 'rfid_tag')->ignore($this->patronIdBeingEdited)],
            'p_first_name' => ['required', 'string', 'max:50', $fullNameUniqueRule],
            'p_middle_name' => 'required|string|max:50',
            'p_last_name' => 'required|string|max:50',
            'p_suffix' => 'nullable|string|max:10',
            'p_patron_type_id' => 'required|exists:patron_types,id',
            'p_grade_level_id' => $isStudent ? 'required|exists:grade_levels,id' : 'nullable|exists:grade_levels,id',
            'p_section_id' => $isStudent ? 'required|exists:sections,id' : 'nullable|exists:sections,id',
            'p_email' => ['required', 'email', 'max:100', Rule::unique('patrons', 'email')->ignore($this->patronIdBeingEdited)],
            'p_contact_number' => ['required', 'string', 'max:20', Rule::unique('patrons', 'contact_number')->ignore($this->patronIdBeingEdited)],
            'p_address' => 'required|string|max:255',
            'p_status' => 'required|in:active,inactive,suspended',
        ];

        $this->validate($rules, [], [
            'p_first_name' => 'First name',
            'p_middle_name' => 'Middle name',
            'p_last_name' => 'Last name',
            'p_suffix' => 'Suffix',
            'p_school_id' => 'Student/Employee #',
            'p_rfid_tag' => 'RFID',
            'p_email' => 'Email',
            'p_contact_number' => 'Contact number',
            'p_address' => 'Address',
            'p_status' => 'Status',
        ]);

        $payload = [
            'school_id' => $this->p_school_id,
            'rfid_tag' => $this->p_rfid_tag,
            'first_name' => $this->p_first_name,
            'middle_name' => $this->p_middle_name,
            'last_name' => $this->p_last_name,
            'suffix' => $this->p_suffix,
            'patron_type_id' => $this->p_patron_type_id,
            'grade_level_id' => $isStudent ? $this->p_grade_level_id : null,
            'section_id' => $isStudent ? $this->p_section_id : null,
            'email' => strtolower($this->p_email),
            'contact_number' => $this->p_contact_number,
            'address' => $this->p_address,
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
                'p_rfid_tag' => 'This RFID tag or School ID is already assigned to another borrower record.',
            ]);
        } catch (Exception $e) {
            Log::error('Error saving patron: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while saving the borrower.', type: 'error');
            return;
        }

        $message = $this->patronIdBeingEdited ? 'Borrower record updated successfully.' : 'Borrower record created successfully.';

        $this->showPatronModal = false;
        $this->resetPatronForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetPatronForm(): void
    {
        $this->reset([
            'patronIdBeingEdited', 'p_school_id', 'p_rfid_tag', 'p_first_name',
            'p_middle_name', 'p_last_name', 'p_suffix', 'p_patron_type_id',
            'p_grade_level_id', 'p_section_id', 'p_email', 'p_contact_number', 'p_address',
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
            $this->dispatch('toast', message: 'Unable to proceed with deletion request.', type: 'error');
        }
    }

    public function deleteRecord(): void
    {
        try {
            if ($this->deleteType === 'user') {
                $user = User::findOrFail($this->idBeingDeleted);

                if ($user->id === Auth::id()) {
                    $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
                    $this->showDeleteModal = false;
                    return;
                }

                if ($user->role !== 'admin' && $user->role !== 'librarian') {
                    $user->delete();
                    $this->dispatch('toast', message: 'User account deleted successfully.', type: 'success');
                } else {
                    $this->dispatch('toast', message: 'Admin or Librarian accounts cannot be deleted from this view.', type: 'error');
                }
            } elseif ($this->deleteType === 'borrower') {
                Patron::findOrFail($this->idBeingDeleted)->delete();
                $this->dispatch('toast', message: 'Borrower record deleted successfully.', type: 'success');
            }
        } catch (QueryException $e) {
            Log::error('Query error deleting record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot delete record: It is referenced by active transactions or borrower logs.', type: 'error');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred while deleting the record.', type: 'error');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteType', 'idBeingDeleted']);
    }

    #[Layout('components.layouts.app')]
    #[Title('Registrations')]
    public function render()
    {
        try {
            $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $users = $this->activeTab === 'users'
                ? User::query()
                    ->when($this->search, function ($query) use ($likeOperator) {
                        $query->where(function ($q) use ($likeOperator) {
                            $q->where('username', $likeOperator, "%{$this->search}%")
                                ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('email', $likeOperator, "%{$this->search}%")
                                ->orWhere('address', $likeOperator, "%{$this->search}%");
                        });
                    })
                    ->latest()
                    ->paginate(10, ['*'], 'usersPage')
                : new LengthAwarePaginator([], 0, 10);

            $patrons = $this->activeTab === 'borrowers'
                ? Patron::with(['patronType', 'gradeLevel', 'section'])
                    ->when($this->search, function ($query) use ($likeOperator) {
                        $query->where(function ($q) use ($likeOperator) {
                            $q->where('school_id', $likeOperator, "%{$this->search}%")
                                ->orWhere('rfid_tag', $likeOperator, "%{$this->search}%")
                                ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('middle_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('last_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('email', $likeOperator, "%{$this->search}%")
                                ->orWhere('address', $likeOperator, "%{$this->search}%");
                        });
                    })
                    ->latest()
                    ->paginate(10, ['*'], 'patronsPage')
                : new LengthAwarePaginator([], 0, 10);

            $patronTypes = PatronType::orderBy('name')->get(['id', 'name']);
            $allGradeLevels = GradeLevel::orderBy('name')->get(['id', 'name', 'code']);
            $availableSections = $this->p_grade_level_id
                ? Section::where('grade_level_id', $this->p_grade_level_id)->orderBy('name')->get(['id', 'name'])
                : collect();

            $selectedPatronType = $patronTypes->firstWhere('id', $this->p_patron_type_id);
            $isStudentType = $selectedPatronType && strtolower($selectedPatronType->name) === 'student';
        } catch (Exception $e) {
            Log::error('Error rendering registrations view: ' . $e->getMessage());
            $users = new LengthAwarePaginator([], 0, 10);
            $patrons = new LengthAwarePaginator([], 0, 10);
            $patronTypes = collect();
            $allGradeLevels = collect();
            $availableSections = collect();
            $isStudentType = false;
        }

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
