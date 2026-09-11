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

    // Define operating hours
    private string $startTime = '09:00'; // 9:00 AM
    private string $endTime = '17:00'; // 5:00 PM

    #[Layout('components.layouts.kiosk')]
    #[Title('Borrower RFID Kiosk')]
    public function scanRfid(?string $rfid = null): void
    {
        try {
            // 1. Time Restriction Check
            $now = now();
            $start = now()->setTimeFromTimeString($this->startTime);
            $end = now()->setTimeFromTimeString($this->endTime);

            if (!$now->between($start, $end)) {
                session()->flash('kiosk_error', 'Kiosk is outside operating hours (' . $start->format('h:i A') . ' - ' . $end->format('h:i A') . ').');
                return;
            }

            $targetRfid = $rfid ?? $this->rfid_number;
            $trimmedRfid = trim($targetRfid);

            $this->rfid_number = '';

            if (empty($trimmedRfid)) {
                return;
            }

            // 2. Input Security & Length Validation
            if (strlen($trimmedRfid) > 100) {
                session()->flash('kiosk_error', 'Invalid input format detected.');
                return;
            }

            // 3. Secure Query Scope Grouping
            $patron = Patron::with(['patronType', 'gradeLevel', 'section'])
                ->where(function ($query) use ($trimmedRfid) {
                    $query->where('rfid_tag', $trimmedRfid)
                          ->orWhere('school_id', $trimmedRfid);
                })
                ->first();

            if (!$patron) {
                session()->flash('kiosk_error', 'Invalid or unregistered RFID card scanned.');
                return;
            }

            if ($patron->status !== 'active') {
                session()->flash('kiosk_error', 'Borrower account is currently inactive.');
                return;
            }

            $today = now()->toDateString();

            // 4. Database Transaction with Row Locking (Prevents Race Conditions & Crashes)
            DB::transaction(function () use ($patron, $today) {
                $activeLog = PatronLog::where('patron_id', $patron->id)
                    ->where('log_date', $today)
                    ->whereNull('time_out')
                    ->latest()
                    ->lockForUpdate()
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
            });

            $this->scannedPatron = $patron;
            $this->actionTime = now()->format('h:i:s A');
            $this->showResultModal = true;

            $this->dispatch('patron-scanned');

        } catch (\Exception $e) {
            Log::error('Kiosk scan error: ' . $e->getMessage());
            session()->flash('kiosk_error', 'An unexpected system error occurred. Please try scanning again.');
        }
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
