<?php

namespace App\Livewire\Components;

use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Header extends Component
{
    public bool $showProfileModal = false;
    public string $activeTab = 'profile';

    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public ?string $suffix = '';
    public ?string $address = '';
    public ?string $contact_number = '';
    public string $email = '';
    public string $username = '';
    public string $role = '';
    public string $current_password_for_profile = '';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $this->loadUserData();
    }

    public function loadUserData(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->first_name = $user->first_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->suffix = $user->suffix ?? '';
        $this->address = $user->address ?? '';
        $this->contact_number = $user->contact_number ?? '';
        $this->email = $user->email ?? '';
        $this->username = $user->username ?? '';
        $this->role = str($user->role ?? '')->replace('_', ' ')->title();
    }

    public function openProfileModal(string $tab = 'profile'): void
    {
        $this->resetValidation();
        $this->loadUserData();

        $this->current_password_for_profile = '';
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->activeTab = $tab;
        $this->showProfileModal = true;
    }

    public function closeProfileModal(): void
    {
        $this->showProfileModal = false;
        $this->resetValidation();
    }

    public function updateProfile()
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        // 1. Trim inputs BEFORE validation
        $this->first_name = trim($this->first_name);
        $this->middle_name = trim($this->middle_name);
        $this->last_name = trim($this->last_name);
        $this->suffix = $this->suffix ? trim($this->suffix) : null;
        $this->address = $this->address ? trim($this->address) : null;
        $this->contact_number = $this->contact_number ? trim($this->contact_number) : null;
        $this->email = strtolower(trim($this->email));
        $this->username = trim($this->username);

        try {
            $this->validate([
                'first_name' => 'required|string|max:50',
                'middle_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'suffix' => 'nullable|string|max:10',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'username' => [
                    'required',
                    'string',
                    'min:3',
                    'max:20',
                    Rule::unique('users', 'username')->ignore($user->id),
                ],
                'current_password_for_profile' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($user) {
                        if (! Hash::check($value, $user->password)) {
                            $fail('The password you entered is incorrect.');
                        }
                    },
                ],
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: 'Failed to update profile. Please check the form errors.', type: 'error');
            throw $e;
        }

        $emailChanged = strtolower($user->email) !== $this->email;

        try {
            $user->forceFill([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'address' => $this->address,
                'contact_number' => $this->contact_number,
                'email' => $this->email,
                'username' => $this->username,
                'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
            ])->save();
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'email' => 'This email or username is already in use by another account.',
            ]);
        } catch (QueryException $e) {
            Log::error('Profile update DB error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Database error occurred while updating profile.', type: 'error');
            return;
        }

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
                session()->flash('status', 'Profile updated! Please verify your new email address.');
            } catch (Exception $e) {
                session()->flash('status', 'Profile updated, but sending email verification failed. Please check your connection and try again.');
                Log::error('Header updateProfile email verification failed: ' . $e->getMessage());
            }

            return $this->redirectRoute('verification.notice', navigate: true);
        }

        $this->current_password_for_profile = '';
        $this->closeProfileModal();
        $this->dispatch('toast', message: 'Profile details updated successfully!', type: 'success');
    }

    public function updatePassword(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        try {
            $this->validate([
                'current_password' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($user) {
                        if (! Hash::check($value, $user->password)) {
                            $fail('Your current password is incorrect.');
                        }
                    },
                ],
                'new_password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: 'Failed to change password. Please check form inputs.', type: 'error');
            throw $e;
        }

        try {
            $user->forceFill([
                'password' => Hash::make($this->new_password),
            ])->save();
        } catch (QueryException $e) {
            Log::error('Password update DB error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Database error occurred while updating password.', type: 'error');
            return;
        }

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->closeProfileModal();
        $this->dispatch('toast', message: 'Password updated successfully!', type: 'success');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.components.header');
    }
}
