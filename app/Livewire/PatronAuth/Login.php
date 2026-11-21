<?php // app/Livewire/PatronAuth/Login.php

namespace App\Livewire\PatronAuth;

use App\Models\Patron;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
        ], [], [
            'borrowerNumber' => 'Student / Employee ID or RFID Tag',
        ]);

        try {
            $op = $this->likeOperator();

            $patron = Patron::with('patronType')
                ->where(function ($query) use ($op, $cleanId) {
                    $query->where('school_id', $op, $cleanId)
                        ->orWhere('rfid_tag', $op, $cleanId);
                })
                ->first();

            if (! $patron) {
                $this->addError('borrowerNumber', 'Student / Employee ID or RFID tag not found.');
                return;
            }

            if (strtolower((string) ($patron->status ?? '')) !== 'active') {
                $this->addError('borrowerNumber', 'Your library account is currently inactive. Please approach the librarian.');
                return;
            }

            Session::regenerate();
            Session::put('patron_session_id', $patron->id);

            $this->dispatch('toast', type: 'success', message: 'Welcome to the Library Portal!');
            $this->redirect(route('patron.portal'), navigate: true);
        } catch (Throwable $e) {
            Log::error('Failed patron login attempt in PatronAuth\\Login::login: ' . $e->getMessage(), [
                'borrower_number' => $cleanId,
                'exception'       => $e,
            ]);
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
