<?php // app/Livewire/PatronAuth/Login.php

namespace App\Livewire\PatronAuth;

use App\Models\Patron;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

class Login extends Component
{
    public string $borrowerNumber = '';

    public function mount(): void
    {
        try {
            if (Session::has('patron_session_id')) {
                $this->redirect(route('patron.portal'), navigate: true);
            }
        } catch (Throwable $e) {
            Log::error('Failed checking patron session in PatronAuth\\Login::mount: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    public function login(): void
    {
        $cleanId = trim(strip_tags($this->borrowerNumber));
        $this->borrowerNumber = $cleanId;

        $this->validate([
            'borrowerNumber' => 'required|string|max:50',
        ], [], ['borrowerNumber' => 'Student / Employee ID or RFID Tag']);

        $throttleKey = 'patron-login:' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('borrowerNumber', "Too many failed attempts. Please wait {$seconds} seconds.");
            return;
        }

        try {
            $lower = Str::lower($cleanId);

            $patron = Patron::with('patronType')
                ->where(fn ($q) => $q->whereRaw('LOWER(school_id) = ?', [$lower])
                                    ->orWhereRaw('LOWER(rfid_tag) = ?', [$lower]))
                ->first();

            if (! $patron || strtolower((string) $patron->status) !== 'active') {
                RateLimiter::hit($throttleKey, 60);
                $this->addError('borrowerNumber', 'ID not found or account inactive. Please approach the librarian if you need help.');
                return;
            }

            RateLimiter::clear($throttleKey);
            Session::regenerate();
            Session::put('patron_session_id', $patron->id);
            Session::put('patron_last_activity', now()->timestamp);

            $this->dispatch('toast', type: 'success', message: 'Welcome to the Library Portal!');
            $this->redirect(route('patron.portal'), navigate: true);
        } catch (Throwable $e) {
            Log::error('Patron login failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->dispatch('toast', type: 'error', message: 'Unable to process patron sign-in right now.');
        }
    }

    #[Layout('components.layouts.guest')]
    #[Title('Patron Login')]
    public function render()
    {
        return view('livewire.patron-auth.login');
    }
}
