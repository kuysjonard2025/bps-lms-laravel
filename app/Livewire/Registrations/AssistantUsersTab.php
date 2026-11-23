<?php // app/Livewire/Registrations/AssistantUsersTab.php

namespace App\Livewire\Registrations;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class AssistantUsersTab extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public bool $showUserModal = false;
    public bool $showDeleteModal = false;
    public ?int $idBeingDeleted = null;

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

    private function toTitle(string $value): string
    {
        return mb_convert_case(trim($value), MB_CASE_TITLE, 'UTF-8');
    }

    private function throwUserUniqueValidation(UniqueConstraintViolationException $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'users_full_name_unique')) {
            throw ValidationException::withMessages([
                'u_first_name' => 'A User With This Full Name Already Exists.',
            ]);
        }

        if (str_contains($message, 'users_email_unique') || str_contains($message, 'email')) {
            throw ValidationException::withMessages([
                'u_email' => 'This Email Address Is Already In Use.',
            ]);
        }

        if (str_contains($message, 'users_contact_number_unique') || str_contains($message, 'contact_number')) {
            throw ValidationException::withMessages([
                'u_contact_number' => 'This Contact Number Is Already In Use.',
            ]);
        }

        if (str_contains($message, 'users_username_unique') || str_contains($message, 'username')) {
            throw ValidationException::withMessages([
                'u_username' => 'This Username Is Already Taken.',
            ]);
        }

        throw ValidationException::withMessages([
            'u_username' => 'A Database Unique Constraint Error Occurred While Saving This User.',
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage('usersPage');
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
        $this->cleanFields([
            'u_first_name', 'u_middle_name', 'u_last_name', 'u_suffix',
            'u_username', 'u_email', 'u_contact_number', 'u_address',
        ]);

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

        $attributes = [
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
        ];

        $validated = $this->validate($rules, [], $attributes);

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
            $createdUser = null;

            DB::transaction(function () use ($data, &$createdUser): void {
                if ($this->userIdBeingEdited) {
                    $user = User::query()->lockForUpdate()->findOrFail($this->userIdBeingEdited);
                    $user->update($data);
                } else {
                    $createdUser = User::query()->create($data);
                }
            });

            if ($createdUser) {
                $createdUser->sendEmailVerificationNotification();
                $message = Str::headline($role) . ' User Created Successfully. Verification Email Dispatched.';
            } else {
                $message = Str::headline($role) . ' User Updated Successfully.';
            }
        } catch (UniqueConstraintViolationException $e) {
            $this->throwUserUniqueValidation($e);
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

    public function confirmDelete(int $id): void
    {
        $this->idBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        try {
            DB::transaction(function (): void {
                $user = User::query()->lockForUpdate()->findOrFail($this->idBeingDeleted);

                if ($user->id === Auth::id()) {
                    $this->dispatch('toast', message: 'You Cannot Delete Your Own Account.', type: 'error');
                    return;
                }

                if ($user->role === 'admin' || $user->role === 'librarian') {
                    $this->dispatch('toast', message: 'Admin Or Librarian Accounts Cannot Be Deleted From This View.', type: 'error');
                    return;
                }

                $user->delete();
                $this->dispatch('toast', message: 'User Account Deleted Successfully.', type: 'success');
            });
        } catch (QueryException $e) {
            Log::error('Query error deleting user: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Cannot Delete Record: It Is Referenced By Active Transactions Or Logs.', type: 'error');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting user: ' . $e->getMessage());
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

            $users = User::query()
                ->when($term !== '', function ($query) use ($operator, $term) {
                    $query->where(function ($q) use ($operator, $term) {
                        $q->where('username', $operator, "%{$term}%")
                            ->orWhere('first_name', $operator, "%{$term}%")
                            ->orWhere('middle_name', $operator, "%{$term}%")
                            ->orWhere('last_name', $operator, "%{$term}%")
                            ->orWhere('email', $operator, "%{$term}%")
                            ->orWhere('address', $operator, "%{$term}%");
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'usersPage');
        } catch (Exception $e) {
            Log::error('Error rendering assistant users tab: ' . $e->getMessage());
            $users = new LengthAwarePaginator([], 0, 10);
        }

        return view('livewire.registrations.assistant-users-tab', [
            'users' => $users,
        ]);
    }
}
