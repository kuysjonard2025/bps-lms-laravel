<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
        $this->validate([
            'umail' => 'required|string|max:100',
            'password' => 'required|string',
        ], [
            'umail.required' => 'Username or email field is required.',
        ]);

        $umail = trim($this->umail);

        // Key = username/email + IP, normalized so casing can't create new buckets
        $throttleKey = Str::transliterate('login-attempt:' . Str::lower($umail) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            // Fire Lockout only once per lockout window so the auth log isn't flooded
            if (Cache::add("lockout-logged:{$throttleKey}", true, $seconds)) {
                event(new Lockout(request()));
            }

            $this->addError(
                'warning',
                "Too many failed login attempts. Please wait {$seconds} seconds before trying again."
            );
            $this->reset('password');

            return;
        }

        $field = filter_var($umail, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $umail, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($throttleKey);

            session()->regenerate();

            return $this->redirectIntended(default: route('dashboard'), navigate: true);
        }

        RateLimiter::hit($throttleKey, 60);

        $this->reset('password');
        $this->addError('warning', 'The provided credentials do not match our records.');
    }

    #[Layout('components.layouts.guest')]
    #[Title('Login')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
