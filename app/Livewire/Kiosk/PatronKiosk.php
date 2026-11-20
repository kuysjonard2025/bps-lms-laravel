<?php

namespace App\Livewire\Kiosk;

use App\Models\Patron;
use App\Models\PatronLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public int $scanSequence = 0;

    private string $startTime = '07:00';
    private string $endTime = '17:00';

    private function hasValidKioskSession(): bool
    {
        return session()->get('kiosk_authenticated') === true
            && session()->has('kiosk_session.user_id');
    }

    public function mount(): void
    {
        if (! $this->hasValidKioskSession()) {
            $this->redirectRoute('kiosk.login');
        }
    }

    #[Layout('components.layouts.kiosk')]
    #[Title('Patron RFID Kiosk')]
    public function scanRfid(?string $rfid = null): void
    {
        try {
            if (! $this->hasValidKioskSession()) {
                $this->redirectRoute('kiosk.login');
                return;
            }

            $now = now();
            $start = now()->setTimeFromTimeString($this->startTime);
            $end = now()->setTimeFromTimeString($this->endTime);

            if (! $now->between($start, $end)) {
                session()->flash('kiosk_error', 'Kiosk Is Outside Operating Hours (' . $start->format('h:i A') . ' - ' . $end->format('h:i A') . ').');
                return;
            }

            $targetRfid = $rfid ?? $this->rfid_number;
            $trimmedRfid = trim($targetRfid);

            $this->rfid_number = '';

            if ($trimmedRfid === '') {
                return;
            }

            if (strlen($trimmedRfid) > 100) {
                session()->flash('kiosk_error', 'Invalid Input Format Detected.');
                return;
            }

            $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $patron = Patron::with(['patronType', 'department', 'gradeLevel', 'section'])
                ->where(function ($query) use ($trimmedRfid, $likeOperator) {
                    $query->where('rfid_tag', $likeOperator, $trimmedRfid)
                        ->orWhere('school_id', $likeOperator, $trimmedRfid);
                })
                ->first();

            if (! $patron) {
                session()->flash('kiosk_error', 'Invalid Or Unregistered RFID Card Scanned.');
                return;
            }

            if ($patron->status !== 'active') {
                session()->flash('kiosk_error', 'Patron Account Is Currently Inactive.');
                return;
            }

            $today = now()->toDateString();
            $status = '';

            DB::transaction(function () use ($patron, $today, &$status) {
                $activeLog = PatronLog::where('patron_id', $patron->id)
                    ->where('log_date', $today)
                    ->whereNull('time_out')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if ($activeLog) {
                    $activeLog->update(['time_out' => now()]);
                    $status = 'LOGGED OUT';
                } else {
                    PatronLog::create([
                        'patron_id' => $patron->id,
                        'time_in'   => now(),
                        'log_date'  => $today,
                    ]);
                    $status = 'LOGGED IN';
                }
            });

            // Immediately replace any currently displayed scan and reset the 10-second timer
            $this->scannedPatron = $patron;
            $this->actionStatus = $status;
            $this->actionTime = now()->format('h:i:s A');
            $this->scanSequence++;
            $this->showResultModal = true;

            $this->dispatch('patron-scanned', sequence: $this->scanSequence);
        } catch (\Exception $e) {
            Log::error('Kiosk scan error: ' . $e->getMessage());
            session()->flash('kiosk_error', 'An Unexpected System Error Occurred. Please Try Scanning Again.');
        }
    }

    public function closeResultModal(): void
    {
        $this->showResultModal = false;
        $this->scannedPatron = null;
        $this->actionStatus = '';
        $this->actionTime = '';
    }

    public function exitKiosk(): void
    {
        session()->forget(['kiosk_authenticated', 'kiosk_session', 'kiosk_unlocked_by']);
        $this->redirectRoute('kiosk.login');
    }

    public function render()
    {
        return view('livewire.kiosk.patron-kiosk');
    }
}
