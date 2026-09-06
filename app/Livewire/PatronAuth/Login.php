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

    public function mount(): void
    {
        if (Session::has('patron_session_id')) {
            $this->redirect(route('patron.portal'), navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate([
            'patronId' => 'required|string|max:50',
        ]);

        $cleanId = trim($this->patronId);

        // Search by School ID or RFID Tag
        $patron = Patron::where('school_id', $cleanId)
            ->orWhere('rfid_tag', $cleanId)
            ->first();

        if (! $patron) {
            $this->addError('patronId', 'Student / Employee ID or RFID tag not found.');
            return;
        }

        if (strtolower($patron->status) !== 'active') {
            $this->addError('patronId', 'Your library account is currently inactive. Please approach the librarian.');
            return;
        }

        Session::regenerate();
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
