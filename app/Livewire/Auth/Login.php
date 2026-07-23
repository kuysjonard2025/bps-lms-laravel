<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Login extends Component
{
    public string $umail = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        // 1. Generate unique key for the IP + endpoint
        $throttleKey = 'login-attempt:' . request()->ip();

        // 2. Block execution if attempts exceed limit (5 attempts)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError(
                'warning',
                "Too many failed login attempts. Please wait {$seconds} seconds before trying again."
            );

            return;
        }

        $this->validate([
            'umail' => 'required',
            'password' => 'required',
        ], [
            'umail.required' => 'Username or email field is required.',
        ]);

        // Check if input is email or username
        $field = filter_var($this->umail, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $this->umail, 'password' => $this->password], $this->remember)) {
            // Clear attempts on success
            RateLimiter::clear($throttleKey);

            session()->regenerate();

            return $this->redirectIntended(default: route('dashboard'), navigate: true);
        }

        // 3. Increment failed attempts (expires after 60 seconds)
        RateLimiter::hit($throttleKey, 60);

        $this->addError('warning', 'The provided credentials do not match our records.');
    }

    #[Layout('components.layouts.guest')]
    #[Title('Login')]
    public function render()
    {
        return view('livewire.auth.login');
    }

}
