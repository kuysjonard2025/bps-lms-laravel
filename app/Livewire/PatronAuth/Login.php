<?php

namespace App\Livewire\PatronAuth;

use App\Models\Patron;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Login extends Component
{
    public string $patronId = '';

    public function login(): void
    {
        $this->validate([
            'patronId' => 'required|string',
        ]);

        $patron = Patron::where('patron_id', trim($this->patronId))->first();

        if (! $patron) {
            $this->addError('patronId', 'Patron ID not found in library record.');
            return;
        }

        if ($patron->status !== 'active') {
            $this->addError('patronId', 'Your patron account is currently inactive. Please approach the library desk.');
            return;
        }

        // Store patron session identifier
        Session::put('patron_session_id', $patron->id);

        $this->dispatch('toast', message: 'Welcome to the Library Portal!', type: 'success');
        $this->redirect(route('patron.portal'), navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Patron Login')]
    public function render()
    {
        return view('livewire.patron-auth.login');
    }
}
