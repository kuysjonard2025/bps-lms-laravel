<?php

namespace App\Livewire;

use App\Models\KioskSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class KioskSettingsManager extends Component
{
    public string $terminal_name = 'Main Library Kiosk';
    public string $pin = '';
    public string $pin_confirmation = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->ensureMainUserAccess();

        $setting = KioskSetting::orderBy('id')->first();
        if ($setting) {
            $this->terminal_name = $setting->terminal_name;
            $this->is_active = (bool) $setting->is_active;
        }
    }

    private function ensureMainUserAccess(): void
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, ['librarian', 'admin'], true)) {
            abort(403, 'Unauthorized. Only The Main User Can Configure Kiosk Settings.');
        }
    }

    public function saveKioskSettings(): void
    {
        $this->ensureMainUserAccess();

        $rules = [
            'terminal_name'    => ['required', 'string', 'max:100'],
            'pin'              => ['required', 'string', 'min:4', 'max:50', 'confirmed'],
            'pin_confirmation' => ['required', 'string', 'min:4', 'max:50'],
            'is_active'        => ['required', 'boolean'],
        ];

        $this->validate($rules, [], [
            'terminal_name'    => 'Terminal Name',
            'pin'              => 'Kiosk Access PIN',
            'pin_confirmation' => 'Confirm Kiosk Access PIN',
            'is_active'        => 'Active Status',
        ]);

        try {
            $setting = KioskSetting::orderBy('id')->first();

            $payload = [
                'terminal_name'    => trim($this->terminal_name),
                'access_code_hash' => Hash::make($this->pin),
                'updated_by'       => Auth::id(),
                'is_active'        => $this->is_active,
            ];

            if ($setting) {
                $setting->update($payload);
            } else {
                KioskSetting::create($payload);
            }

            $this->reset(['pin', 'pin_confirmation']);
            $this->dispatch('toast', message: 'Kiosk Access PIN Updated Successfully.', type: 'success');
        } catch (\Exception $e) {
            Log::error('Error saving kiosk settings: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Failed To Update Kiosk Access PIN.', type: 'error');
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Kiosk Settings')]
    public function render(): View
    {
        $setting = KioskSetting::with('updatedBy')->orderBy('id')->first();

        return view('livewire.kiosk-settings-manager', [
            'setting' => $setting,
        ]);
    }
}
