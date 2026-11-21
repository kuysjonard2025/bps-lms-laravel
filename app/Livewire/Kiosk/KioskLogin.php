<?php

namespace App\Livewire\Kiosk;

use App\Models\KioskSetting;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
#[Title('Kiosk Terminal Access')]
class KioskLogin extends Component
{
    public string $login = '';
    public string $password = '';
    public string $pin = '';

    private function hasKioskSession(): bool
    {
        return session()->get('kiosk_authenticated') === true
            && session()->has('kiosk_session.user_id');
    }

    public function mount(): void
    {
        if ($this->hasKioskSession()) {
            $this->redirectRoute('kiosk.patron-log');
        }
    }

    public function authenticate(): void
    {
        $this->validate([
            'login'    => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'pin'      => ['required', 'string', 'max:50'],
        ], [], [
            'login'    => 'Username or Email',
            'password' => 'Account Password',
            'pin'      => 'Kiosk Access PIN',
        ]);

        $loginValue  = Str::lower(trim($this->login));
        $throttleKey = Str::transliterate('kiosk-login:' . $loginValue . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout(request()));
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('login', "Too many failed attempts. Please wait {$seconds} seconds.");
            $this->reset('password', 'pin');
            return;
        }

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [$loginValue])
            ->orWhereRaw('LOWER(email) = ?', [$loginValue])
            ->first();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->failAttempt($throttleKey, 'login', 'Invalid Staff Credentials.');
            return;
        }

        if (! $user->hasAnyRole(['librarian', 'assistant'])) {
            $this->failAttempt($throttleKey, 'login', 'Only Librarian Or Assistant Staff Can Unlock The Kiosk.');
            return;
        }

        $kioskSetting = KioskSetting::where('is_active', true)->orderBy('id')->first();

        if (! $kioskSetting || ! Hash::check($this->pin, $kioskSetting->access_code_hash)) {
            $this->failAttempt($throttleKey, 'pin', 'Invalid Kiosk Access PIN.');
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        session()->put('kiosk_session', [
            'user_id'      => $user->id,
            'role'         => $user->getRoleNames()->first(),
            'full_name'    => trim("{$user->first_name} {$user->last_name}"),
            'logged_in_at' => now()->toDateTimeString(),
        ]);
        session()->put('kiosk_authenticated', true);
        session()->put('kiosk_unlocked_by', $user->id);

        $this->redirectRoute('kiosk.patron-log');
    }

    private function failAttempt(string $key, string $field, string $message): void
    {
        RateLimiter::hit($key, 60);
        $this->addError($field, $message);
        $this->reset('password', 'pin');
    }

    public function render()
    {
        return view('livewire.kiosk.kiosk-login');
    }
}
