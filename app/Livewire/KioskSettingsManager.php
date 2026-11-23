<?php // app/Livewire/KioskSettingsManager.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\KioskSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class KioskSettingsManager extends Component
{
    use SanitizesInputs;

    public string $terminal_name = 'Main Library Kiosk';
    public string $pin = '';
    public string $pin_confirmation = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->ensureMainUserAccess();

        try {
            $setting = KioskSetting::query()->orderBy('id')->first();
            if ($setting) {
                $this->terminal_name = (string) ($setting->terminal_name ?? 'Main Library Kiosk');
                $this->is_active = (bool) $setting->is_active;
            }
        } catch (\Throwable $e) {
            Log::error('Error loading kiosk settings on mount: ' . $e->getMessage());
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
        $this->cleanFields(['terminal_name']);

        $rules = [
            'terminal_name'    => ['required', 'string', 'max:100'],
            'pin'              => ['required', 'string', 'min:4', 'max:50', 'same:pin_confirmation'],
            'pin_confirmation' => ['required', 'string', 'min:4', 'max:50'],
            'is_active'        => ['required', 'boolean'],
        ];

        $validated = $this->validate($rules, [], [
            'terminal_name'    => 'Terminal Name',
            'pin'              => 'Kiosk Access PIN',
            'pin_confirmation' => 'Confirm Kiosk Access PIN',
            'is_active'        => 'Active Status',
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $setting = KioskSetting::query()->lockForUpdate()->orderBy('id')->first();

                $payload = [
                    'terminal_name'    => trim((string) $validated['terminal_name']),
                    'access_code_hash' => Hash::make((string) $validated['pin']),
                    'updated_by'       => Auth::id(),
                    'is_active'        => (bool) $validated['is_active'],
                ];

                if ($setting) {
                    $setting->update($payload);
                } else {
                    KioskSetting::query()->create($payload);
                }
            });

            $this->reset(['pin', 'pin_confirmation']);
            $this->resetValidation();
            $this->dispatch('toast', message: 'Kiosk Access PIN Updated Successfully.', type: 'success');
        } catch (\Throwable $e) {
            Log::error('Error saving kiosk settings: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Failed To Update Kiosk Access PIN.', type: 'error');
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Kiosk Settings')]
    public function render(): View
    {
        $setting = null;

        try {
            $setting = KioskSetting::query()
                ->with('updatedBy')
                ->orderBy('id')
                ->first();
        } catch (\Throwable $e) {
            Log::error('Error rendering kiosk settings: ' . $e->getMessage());
        }

        return view('livewire.kiosk-settings-manager', [
            'setting' => $setting,
        ]);
    }
}
