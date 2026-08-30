<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

class CompleteProfile extends Component
{
    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public ?string $suffix = '';
    public ?string $address = '';
    public ?string $contact_number = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->first_name = $user->first_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->suffix = $user->suffix ?? '';
        $this->address = $user->address ?? '';
        $this->contact_number = $user->contact_number ?? '';
        $this->email = $user->email ?? '';
        $this->username = $user->username ?? '';
    }

    public function updateProfile()
    {
        /** @var User $user */
        $user = Auth::user();

        $firstName = strtolower(trim($this->first_name)) ?: null;
        $middleName = strtolower(trim($this->middle_name)) ?: null;
        $lastName = strtolower(trim($this->last_name)) ?: null;
        $suffix = strtolower(trim($this->suffix)) ?: null;

        // Compound unique rule for [first_name, middle_name, last_name, suffix]
        $fullNameRule = Rule::unique('users', 'first_name')
            ->where('middle_name', $middleName)
            ->where('last_name', $lastName)
            ->where('suffix', $suffix)
            ->ignore($user->id);

        $this->validate([
            'first_name'     => ['nullable', 'string', 'max:50', $fullNameRule],
            'middle_name'    => 'nullable|string|max:50',
            'last_name'      => 'nullable|string|max:50',
            'suffix'         => 'nullable|string|max:10',
            'address'        => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'email'          => [
                'required', 'email', 'max:100',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'username'       => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'password'       => 'nullable|string|min:6|max:20|confirmed',
        ], [
            'first_name.unique' => 'An account with this full name and suffix already exists.',
            'password.min'      => 'The password must be at least 6 characters.',
            'password.max'      => 'The password must not exceed 20 characters.',
        ]);

        $normalizedNewEmail = strtolower(trim($this->email));
        $normalizedOldEmail = strtolower(trim((string) $user->email));

        $emailChanged = $normalizedOldEmail !== '' && $normalizedNewEmail !== $normalizedOldEmail;

        $updateData = [
            'first_name'     => $firstName ? Str::title($firstName) : null,
            'middle_name'    => $middleName ? Str::title($middleName) : null,
            'last_name'      => $lastName ? Str::title($lastName) : null,
            'suffix'         => $suffix,
            'address'        => trim($this->address) ?: null,
            'contact_number' => trim($this->contact_number) ?: null,
            'email'          => $normalizedNewEmail,
            'username'       => strtolower(trim($this->username)),
        ];

        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
        }

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);
        $user->refresh();

        // Send Email Verification only if email actually changed or was never verified
        if ($emailChanged || is_null($user->email_verified_at)) {
            try {
                $user->sendEmailVerificationNotification();
                session()->flash('status', 'Profile updated successfully! Please check your email to verify your account.');
            } catch (Throwable $e) {
                session()->flash('status', 'Profile updated, but sending verification email failed. Please try again from the verification page.');
                Log::error('CompleteProfile sendEmailVerificationNotification failed: ' . $e->getMessage());
            }

            return $this->redirectRoute('verification.notice', navigate: true);
        }

        session()->flash('status', 'Profile updated successfully!');

        return $this->redirectRoute('dashboard', navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Complete Profile')]
    public function render()
    {
        return view('livewire.auth.complete-profile');
    }
}
