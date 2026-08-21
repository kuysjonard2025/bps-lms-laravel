<?php

namespace App\Livewire\Kiosk;

use App\Models\Patron;
use App\Models\PatronLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PatronKiosk extends Component
{
    public string $rfid_number = '';
    public bool $showResultModal = false;

    public ?Patron $scannedPatron = null;
    public string $actionStatus = '';
    public string $actionTime = '';

    #[Layout('components.layouts.kiosk')]
    #[Title('Patron RFID Kiosk')]
    public function scanRfid(?string $rfid = null): void
    {
        $targetRfid = $rfid ?? $this->rfid_number;
        $trimmedRfid = trim($targetRfid);

        $this->rfid_number = '';

        if (empty($trimmedRfid)) {
            return;
        }

        $patron = Patron::with(['patronType', 'gradeLevel', 'section'])
            ->where('patron_id', $trimmedRfid)
            ->first();

        if (!$patron) {
            session()->flash('kiosk_error', 'Invalid or unregistered RFID card scanned.');
            return;
        }

        if ($patron->status !== 'active') {
            session()->flash('kiosk_error', 'Patron account is currently inactive.');
            return;
        }

        $today = now()->toDateString();

        $activeLog = PatronLog::where('patron_id', $patron->id)
            ->where('log_date', $today)
            ->whereNull('time_out')
            ->latest()
            ->first();

        if ($activeLog) {
            $activeLog->update(['time_out' => now()]);
            $this->actionStatus = 'LOGGED OUT';
        } else {
            PatronLog::create([
                'patron_id' => $patron->id,
                'time_in'   => now(),
                'log_date'  => $today,
            ]);
            $this->actionStatus = 'LOGGED IN';
        }

        $this->scannedPatron = $patron;
        $this->actionTime = now()->format('h:i:s A');
        $this->showResultModal = true;

        $this->dispatch('patron-scanned');
    }

    public function closeResultModal(): void
    {
        $this->showResultModal = false;
        $this->scannedPatron = null;
    }

    public function exitKiosk(): void
    {
        session()->forget('kiosk_authenticated');
        $this->redirectRoute('kiosk.login');
    }

    public function render()
    {
        return view('livewire.kiosk.patron-kiosk');
    }
}
