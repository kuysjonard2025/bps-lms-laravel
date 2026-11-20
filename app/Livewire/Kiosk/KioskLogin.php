<?php

namespace App\Livewire\Kiosk;

use App\Models\KioskSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    #[Layout('components.layouts.kiosk')]
    #[Title('Kiosk Terminal Access')]
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

        $loginValue = trim($this->login);
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $user = User::query()
            ->where(function ($query) use ($loginValue, $likeOperator) {
                $query->where('username', $likeOperator, $loginValue)
                    ->orWhere('email', $likeOperator, $loginValue);
            })
            ->first();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->addError('login', 'Invalid Staff Credentials.');
            $this->password = '';
            $this->pin = '';
            return;
        }

        if (! in_array($user->role, ['librarian', 'admin', 'assistant'], true)) {
            $this->addError('login', 'Only Librarian Or Assistant Staff Can Unlock The Kiosk.');
            $this->password = '';
            $this->pin = '';
            return;
        }

        $kioskSetting = KioskSetting::where('is_active', true)->orderBy('id')->first();

        if (! $kioskSetting || ! Hash::check($this->pin, $kioskSetting->access_code_hash)) {
            $this->addError('pin', 'Invalid Kiosk Access PIN.');
            $this->pin = '';
            return;
        }

        session()->put('kiosk_session', [
            'user_id'      => $user->id,
            'role'         => $user->role,
            'full_name'    => trim("{$user->first_name} {$user->last_name}"),
            'logged_in_at' => now()->toDateTimeString(),
        ]);
        session()->put('kiosk_authenticated', true);
        session()->put('kiosk_unlocked_by', $user->id);

        $this->redirectRoute('kiosk.patron-log');
    }

    public function render()
    {
        return view('livewire.kiosk.kiosk-login');
    }
}
