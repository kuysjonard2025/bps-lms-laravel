<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class VerifyEmail extends Component
{
    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Redirect immediately if already verified
        if ($user && $user->hasVerifiedEmail()) {
            return $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function resendNotification()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            $this->addError('warning', 'Sending Email Verification failed. Please check your internet connection.');
            Log::error('VerifyEmail Error: ' . $e->getMessage());
            return;
        }

        session()->flash('status', 'verification-link-sent');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirectRoute('login', navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Verify Email')]
    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
