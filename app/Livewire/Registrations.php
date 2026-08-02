<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\GradeLevel;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

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
    public bool $isEditingAdmin = false;
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

    // Empty String Normalizers for FK Dropdowns
    public function updatedPPatronTypeId($value): void
    {
        if ($value === '') {
            $this->p_patron_type_id = null;
        }
    }

    public function updatedPGradeLevelId($value): void
    {
        if ($value === '') {
            $this->p_grade_level_id = null;
        }
        $this->p_section_id = null;
    }

    public function updatedPSectionId($value): void
    {
        if ($value === '') {
            $this->p_section_id = null;
        }
    }

    // Reset pagination when searching or switching tabs
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
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
        $this->isEditingAdmin = ($user->role === 'admin');
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
        $rules = [
            'u_first_name' => 'required|string|max:50',
            'u_middle_name' => 'required|string|max:50',
            'u_last_name' => 'required|string|max:50',
            'u_suffix' => 'nullable|string|max:10',
            'u_username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($this->userIdBeingEdited),
            ],
            'u_role' => 'required|in:admin,librarian,assistant',
            'u_email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('users', 'email')
                    ->ignore($this->userIdBeingEdited)
                    ->when(!trim($this->u_email), fn ($rule) => $rule->whereNull('email')),
            ],
            'u_contact_number' => 'required|string|max:20',
            'u_address' => 'required|string|max:255',
            'u_password' => $this->userIdBeingEdited ? 'nullable|min:6' : 'required|min:6',
        ];

        $validated = $this->validate($rules);

        $data = [
            'first_name' => trim($validated['u_first_name']),
            'middle_name' => trim($validated['u_middle_name']),
            'last_name' => trim($validated['u_last_name']),
            'suffix' => trim($validated['u_suffix']) ?: null,
            'username' => trim($validated['u_username']),
            'role' => $this->isEditingAdmin ? 'admin' : $validated['u_role'],
            'email' => trim($validated['u_email']) ?: null,
            'contact_number' => trim($validated['u_contact_number']),
            'address' => trim($validated['u_address']),
        ];

        if (!empty($validated['u_password'])) {
            $data['password'] = Hash::make($validated['u_password']);
        }

        User::updateOrCreate(['id' => $this->userIdBeingEdited], $data);

        $message = $this->userIdBeingEdited ? 'User updated successfully.' : 'User created successfully.';

        $this->showUserModal = false;
        $this->resetUserForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function resetUserForm(): void
    {
        $this->reset([
            'userIdBeingEdited',
            'isEditingAdmin',
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
        // Explicitly map the rule to the 'first_name' column in the database
        $fullNameUniqueRule = Rule::unique('patrons', 'first_name')
            ->where('first_name', trim($this->p_first_name))
            ->where('middle_name', trim($this->p_middle_name))
            ->where('last_name', trim($this->p_last_name))
            ->where('suffix', trim($this->p_suffix) ?: null)
            ->ignore($this->patronIdBeingEdited);

        $rules = [
            'p_patron_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('patrons', 'patron_id')->ignore($this->patronIdBeingEdited),
            ],
            'p_first_name' => ['required', 'string', 'max:50', $fullNameUniqueRule],
            'p_middle_name' => 'required|string|max:50',
            'p_last_name' => 'required|string|max:50',
            'p_suffix' => 'nullable|string|max:10',
            'p_patron_type_id' => 'required|exists:patron_types,id',
            'p_grade_level_id' => 'nullable|exists:grade_levels,id',
            'p_section_id' => 'nullable|exists:sections,id',
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
        ]);

        // Clear grade/section if selected patron type is not a student
        $selectedType = PatronType::find($this->p_patron_type_id);
        $isStudent = $selectedType && strtolower($selectedType->name) === 'student';

        Patron::updateOrCreate(
            ['id' => $this->patronIdBeingEdited],
            [
                'patron_id' => trim($this->p_patron_id),
                'first_name' => trim($this->p_first_name),
                'middle_name' => trim($this->p_middle_name),
                'last_name' => trim($this->p_last_name),
                'suffix' => trim($this->p_suffix) ?: null,
                'patron_type_id' => $this->p_patron_type_id,
                'grade_level_id' => $isStudent ? $this->p_grade_level_id : null,
                'section_id' => $isStudent ? $this->p_section_id : null,
                'email' => trim($this->p_email),
                'contact_number' => trim($this->p_contact_number),
                'address' => trim($this->p_address),
                'status' => $this->p_status,
            ]
        );

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

    public function deleteItem(): void
    {
        if ($this->deleteType === 'user') {
            $user = User::findOrFail($this->idBeingDeleted);
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

        // 1. Fetch Users Data
        $users = User::query()
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
            ->paginate(10, ['*'], 'usersPage');

        // 2. Fetch Patrons Data
        $patrons = Patron::with(['patronType', 'gradeLevel', 'section'])
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
            ->paginate(10, ['*'], 'patronsPage');

        // 3. Dropdown Datasets
        $patronTypes = PatronType::all();
        $allGradeLevels = GradeLevel::all();
        $availableSections = $this->p_grade_level_id
            ? Section::where('grade_level_id', $this->p_grade_level_id)->get()
            : collect();

        // 4. Check if currently selected Patron Type is "Student"
        $selectedPatronType = PatronType::find($this->p_patron_type_id);
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
