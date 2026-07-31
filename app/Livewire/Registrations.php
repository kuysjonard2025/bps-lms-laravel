<?php

namespace App\Livewire;

use App\Models\GradeLevel;
use App\Models\Patron;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Registrations extends Component
{
    use WithPagination;

    public string $activeTab = 'users';
    public string $search = '';

    // Modals
    public bool $showUserModal = false;
    public bool $showPatronModal = false;
    public bool $showDeleteModal = false;

    // Selection/Editing state
    public ?int $userIdBeingEdited = null;
    public ?int $patronIdBeingEdited = null;
    public ?int $itemBeingDeleted = null;
    public string $deleteType = '';

    // User Form Fields
    public string $u_first_name = '';
    public string $u_middle_name = '';
    public string $u_last_name = '';
    public string $u_suffix = '';
    public string $u_username = '';
    public string $u_email = '';
    public string $u_contact_number = '';
    public string $u_address = '';
    public string $u_role = 'assistant';
    public string $u_password = '';
    public bool $isEditingAdmin = false;

    // Patron Form Fields
    public string $p_patron_id = '';
    public string $p_first_name = '';
    public string $p_middle_name = '';
    public string $p_last_name = '';
    public string $p_suffix = '';
    public string $p_type = 'student';
    public string $p_email = '';
    public string $p_contact_number = '';
    public string $p_address = '';
    public ?int $p_grade_level_id = null;
    public ?int $p_section_id = null;
    public string $p_status = 'active';

    public $availableSections = [];

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->search = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPType($value): void
    {
        if ($value === 'staff') {
            $this->p_grade_level_id = null;
            $this->p_section_id = null;
            $this->availableSections = [];
        }
    }

    public function updatedPGradeLevelId($value): void
    {
        if ($value && $this->p_type === 'student') {
            $this->availableSections = Section::where('grade_level_id', $value)->orderBy('name')->get();
        } else {
            $this->availableSections = [];
        }
        $this->p_section_id = null;
    }

    // --- USER ACTIONS ---

    public function openCreateUserModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'u_first_name', 'u_middle_name', 'u_last_name', 'u_suffix',
            'u_username', 'u_email', 'u_contact_number', 'u_address',
            'u_password', 'userIdBeingEdited'
        ]);
        $this->u_role = 'assistant';
        $this->isEditingAdmin = false;
        $this->showUserModal = true;
    }

    public function openEditUserModal(User $user): void
    {
        $this->resetValidation();
        $this->userIdBeingEdited = $user->id;
        $this->u_first_name = $user->first_name ?? '';
        $this->u_middle_name = $user->middle_name ?? '';
        $this->u_last_name = $user->last_name ?? '';
        $this->u_suffix = $user->suffix ?? '';
        $this->u_username = $user->username;
        $this->u_email = $user->email ?? '';
        $this->u_contact_number = $user->contact_number ?? '';
        $this->u_address = $user->address ?? '';
        $this->u_role = $user->role ?? 'assistant';
        $this->u_password = '';
        $this->isEditingAdmin = ($user->role === 'admin');

        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        $targetUser = $this->userIdBeingEdited ? User::find($this->userIdBeingEdited) : null;

        $firstName = trim($this->u_first_name) ?: null;
        $middleName = trim($this->u_middle_name) ?: null;
        $lastName = trim($this->u_last_name) ?: null;
        $suffix = trim($this->u_suffix) ?: null;

        $rules = [
            'u_first_name' => [
                'nullable', 'string', 'max:50',
                Rule::unique('users', 'first_name')
                    ->where(fn ($query) => $query
                        ->where('middle_name', $middleName)
                        ->where('last_name', $lastName)
                        ->where('suffix', $suffix)
                    )
                    ->ignore($this->userIdBeingEdited)
            ],
            'u_middle_name'    => 'required|string|max:50',
            'u_last_name'      => 'required|string|max:50',
            'u_suffix'         => 'nullable|string|max:10',
            'u_username'       => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'username')->ignore($this->userIdBeingEdited)
            ],
            'u_email'          => [
                'nullable', 'email', 'max:100',
                Rule::unique('users', 'email')->ignore($this->userIdBeingEdited)
            ],
            'u_contact_number' => 'required|string|max:20',
            'u_address'        => 'required|string|max:255',
            'u_role'           => ['required', 'string', 'max:20', Rule::in(['librarian', 'assistant', 'admin'])],
        ];

        if (!$this->userIdBeingEdited || !empty($this->u_password)) {
            $rules['u_password'] = 'required|string|min:6';
        }

        $this->validate($rules, [
            'u_first_name.unique' => 'A user with this full name and suffix already exists.'
        ], [
            'u_first_name'     => 'first name',
            'u_middle_name'    => 'middle name',
            'u_last_name'      => 'last name',
            'u_suffix'         => 'suffix',
            'u_address'        => 'address',
            'u_username'       => 'username',
            'u_email'          => 'email address',
            'u_contact_number' => 'contact number',
            'u_role'           => 'role',
            'u_password'       => 'password',
        ]);

        $finalRole = ($targetUser && $targetUser->role === 'admin') ? 'admin' : $this->u_role;

        $data = [
            'first_name'     => $firstName,
            'middle_name'    => $middleName,
            'last_name'      => $lastName,
            'suffix'         => $suffix,
            'username'       => trim($this->u_username),
            'email'          => strtolower(trim($this->u_email)) ?: null,
            'contact_number' => trim($this->u_contact_number) ?: null,
            'address'        => trim($this->u_address) ?: null,
            'role'           => $finalRole,
        ];

        if (!empty($this->u_password)) {
            $data['password'] = Hash::make($this->u_password);
        }

        if ($targetUser) {
            $targetUser->update($data);
            $message = 'User account updated successfully.';
        } else {
            User::create($data);
            $message = 'User account created successfully.';
        }

        $this->showUserModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // --- PATRON ACTIONS ---

    public function openCreatePatronModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'p_patron_id', 'p_first_name', 'p_middle_name', 'p_last_name',
            'p_suffix', 'p_email', 'p_contact_number', 'p_address',
            'p_grade_level_id', 'p_section_id', 'patronIdBeingEdited'
        ]);
        $this->p_type = 'student';
        $this->p_status = 'active';
        $this->availableSections = [];
        $this->showPatronModal = true;
    }

    public function openEditPatronModal(Patron $patron): void
    {
        $this->resetValidation();
        $this->patronIdBeingEdited = $patron->id;
        $this->p_patron_id = $patron->patron_id;
        $this->p_first_name = $patron->first_name;
        $this->p_middle_name = $patron->middle_name ?? '';
        $this->p_last_name = $patron->last_name;
        $this->p_suffix = $patron->suffix ?? '';
        $this->p_type = $patron->type;
        $this->p_email = $patron->email;
        $this->p_contact_number = $patron->contact_number;
        $this->p_address = $patron->address;
        $this->p_grade_level_id = $this->p_type === 'staff' ? null : $patron->grade_level_id;
        $this->p_section_id = $this->p_type === 'staff' ? null : $patron->section_id;
        $this->p_status = $patron->status ?? 'active';

        if ($this->p_type === 'student' && $this->p_grade_level_id) {
            $this->availableSections = Section::where('grade_level_id', $this->p_grade_level_id)->orderBy('name')->get();
        } else {
            $this->availableSections = [];
        }

        $this->showPatronModal = true;
    }

    public function savePatron(): void
    {
        $firstName = trim($this->p_first_name);
        $middleName = trim($this->p_middle_name) ?: null;
        $lastName = trim($this->p_last_name);
        $suffix = trim($this->p_suffix) ?: null;

        $rules = [
            'p_patron_id'      => [
                'required', 'string',
                Rule::unique('patrons', 'patron_id')->ignore($this->patronIdBeingEdited)
            ],
            'p_first_name'     => [
                'required', 'string', 'max:50',
                Rule::unique('patrons', 'first_name')
                    ->where(fn ($query) => $query
                        ->where('middle_name', $middleName)
                        ->where('last_name', $lastName)
                        ->where('suffix', $suffix)
                    )
                    ->ignore($this->patronIdBeingEdited)
            ],
            'p_middle_name'    => 'required|string|max:50',
            'p_last_name'      => 'required|string|max:50',
            'p_suffix'         => 'nullable|string|max:10',
            'p_type'           => 'required|string|in:student,staff',
            'p_address'        => 'required|string|max:255',
            'p_contact_number' => [
                'required', 'string', 'max:20',
                Rule::unique('patrons', 'contact_number')->ignore($this->patronIdBeingEdited)
            ],
            'p_email'          => [
                'required', 'email', 'max:100',
                Rule::unique('patrons', 'email')->ignore($this->patronIdBeingEdited)
            ],
            'p_status'         => 'required|string',
        ];

        if ($this->p_type === 'student') {
            $rules['p_grade_level_id'] = 'required|exists:grade_levels,id';
            $rules['p_section_id']     = 'required|exists:sections,id';
        }

        $this->validate($rules, [
            'p_first_name.unique' => 'A patron with this full name and suffix already exists.'
        ], [
            'p_patron_id'      => 'patron ID',
            'p_first_name'     => 'first name',
            'p_middle_name'    => 'middle name',
            'p_last_name'      => 'last name',
            'p_suffix'         => 'suffix',
            'p_contact_number' => 'contact number',
            'p_email'          => 'email address',
            'p_address'        => 'address',
            'p_type'           => 'patron type',
        ]);

        $data = [
            'patron_id'      => strtoupper(trim($this->p_patron_id)),
            'first_name'     => $firstName,
            'middle_name'    => $middleName,
            'last_name'      => $lastName,
            'suffix'         => $suffix,
            'type'           => $this->p_type,
            'address'        => trim($this->p_address),
            'contact_number' => trim($this->p_contact_number),
            'email'          => strtolower(trim($this->p_email)),
            'grade_level_id' => $this->p_type === 'student' ? ($this->p_grade_level_id ?: null) : null,
            'section_id'     => $this->p_type === 'student' ? ($this->p_section_id ?: null) : null,
            'status'         => $this->p_status,
        ];

        if ($this->patronIdBeingEdited) {
            Patron::findOrFail($this->patronIdBeingEdited)->update($data);
            $message = 'Patron updated successfully.';
        } else {
            Patron::create($data);
            $message = 'Patron registered successfully.';
        }

        $this->showPatronModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // --- DELETE HANDLERS ---

    public function confirmDelete(string $type, int $id): void
    {
        if ($type === 'user') {
            $user = User::find($id);
            if ($user && $user->role === 'admin') {
                $this->dispatch('toast', message: 'Admin accounts cannot be deleted.', type: 'error');
                return;
            }
        }

        $this->deleteType = $type;
        $this->itemBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if ($this->itemBeingDeleted && $this->deleteType) {
            if ($this->deleteType === 'user') {
                $user = User::find($this->itemBeingDeleted);
                if ($user && $user->role === 'admin') {
                    $this->dispatch('toast', message: 'Admin accounts cannot be deleted.', type: 'error');
                    $this->showDeleteModal = false;
                    return;
                }

                User::destroy($this->itemBeingDeleted);
                $this->dispatch('toast', message: 'User deleted successfully.', type: 'success');
            } elseif ($this->deleteType === 'patron') {
                Patron::destroy($this->itemBeingDeleted);
                $this->dispatch('toast', message: 'Patron deleted successfully.', type: 'success');
            }
        }

        $this->showDeleteModal = false;
        $this->itemBeingDeleted = null;
        $this->deleteType = '';
    }

    #[Layout('components.layouts.app')]
    #[Title('Registrations')]
    public function render()
    {
        $users = User::when($this->search && $this->activeTab === 'users', function ($query) {
                $query->where(function ($q) {
                    $q->where('username', 'like', "%{$this->search}%")
                      ->orWhere('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'u_page');

        $patrons = Patron::with(['gradeLevel', 'section'])
            ->when($this->search && $this->activeTab === 'patrons', function ($query) {
                $query->where(function ($q) {
                    $q->where('patron_id', 'like', "%{$this->search}%")
                      ->orWhere('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'p_page');

        return view('livewire.registrations', [
            'users'          => $users,
            'patrons'        => $patrons,
            'allGradeLevels' => GradeLevel::orderBy('name')->get(),
        ]);
    }
}
