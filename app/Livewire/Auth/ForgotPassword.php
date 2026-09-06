<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';
    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->reset('email');
            return;
        }

        $this->addError('email', __($status));
    }

    #[Layout('components.layouts.guest')]
    #[Title('Forgot Password')]
    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
