<?php

namespace App\Livewire\Kiosk;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class KioskLogin extends Component
{
    public string $pin = '';

    public function mount(): void
    {
        // Redirect immediately if already authenticated
        if (session()->has('kiosk_authenticated')) {
            $this->redirectRoute('kiosk.patron-log');
        }
    }

    #[Layout('components.layouts.kiosk')]
    #[Title('Kiosk Terminal Access')]
    public function authenticate(): void
    {
        if ($this->pin === config('app.kiosk_pin', env('KIOSK_ACCESS_PIN', '1234'))) {
            session()->put('kiosk_authenticated', true);
            $this->redirectRoute('kiosk.patron-log');
            return;
        }

        $this->addError('pin', 'Invalid Kiosk Access PIN.');
        $this->pin = '';
    }

    public function render()
    {
        return view('livewire.kiosk.kiosk-login');
    }
}
