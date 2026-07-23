<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class CompleteProfile extends Component
{
    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public ?string $prefix = '';
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
        $this->prefix = $user->prefix;
        $this->address = $user->address;
        $this->contact_number = $user->contact_number;
        $this->email = $user->email ?? '';
        $this->username = $user->username ?? '';
    }

    public function updateProfile()
    {
        /** @var User $user */
        $user = Auth::user();

        // Safely construct composite name uniqueness rule handling NULL prefixes
        $fullNameRule = Rule::unique('users', 'first_name')
            ->where(function ($query) {
                $query->where('middle_name', $this->middle_name)
                    ->where('last_name', $this->last_name);

                if (filled($this->prefix)) {
                    $query->where('prefix', strtoupper($this->prefix));
                } else {
                    $query->whereNull('prefix');
                }
            })
            ->ignore($user->id);

        $this->validate([
            'first_name'     => ['required', 'string', 'min:2', 'max:50', $fullNameRule],
            'middle_name'    => 'required|string|min:2|max:50',
            'last_name'      => 'required|string|min:2|max:50',
            'prefix'         => 'nullable|string|min:2|max:10',
            'address'        => 'required|string|min:10|max:100',
            'contact_number' => 'required|string|min:11|max:20',
            'email'          => "required|email|max:255|unique:users,email,{$user->id}",
            'username'       => "required|string|min:4|max:20|unique:users,username,{$user->id}",
            'password'       => 'nullable|string|min:4|max:20|confirmed',
        ]);

        $emailChanged = strtolower($user->email) !== strtolower($this->email);
        $formattedPrefix = filled($this->prefix) ? strtoupper(trim($this->prefix)) : null;

        $updateData = [
            'first_name'        => Str::title(trim($this->first_name)),
            'middle_name'       => Str::title(trim($this->middle_name)),
            'last_name'         => Str::title(trim($this->last_name)),
            'prefix'            => $formattedPrefix,
            'address'           => ucfirst(trim($this->address)),
            'contact_number'    => trim($this->contact_number),
            'email'             => strtolower(trim($this->email)),
            'username'          => strtolower(trim($this->username)),
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ];

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);

        // Send Email Verification if updated or missing
        if ($emailChanged || is_null($user->email_verified_at)) {
            try {
                $user->sendEmailVerificationNotification();
                session()->flash('status', 'Profile updated successfully! Please check your email to verify your account.');
            } catch (Exception $e) {
                session()->flash('status', 'Profile updated, but sending verification email failed. Please try again from the verification page.');
                Log::error('CompleteProfile sendEmailVerificationNotification failed: ' . $e->getMessage());
            }

            return $this->redirectRoute('verification.notice', navigate: true);
        }

        return $this->redirectRoute('dashboard', navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Complete Profile')]
    public function render()
    {
        return view('livewire.auth.complete-profile');
    }
}
