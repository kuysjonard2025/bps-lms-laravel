{{-- resources/views/livewire/kiosk-settings.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-6">
    <!-- Page Header -->
    <section aria-label="Kiosk terminal settings header" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-surface-card p-5 rounded-2xl border border-surface-border shadow-2xs">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-content-heading">Kiosk Terminal Settings</h1>
            <p class="mt-0.5 text-xs text-content-body">Configure And Rotate The Kiosk Access PIN Used To Unlock Patron Attendance Kiosks.</p>
        </div>
    </section>

    <!-- Current Status Card -->
    <section aria-label="Current kiosk status" class="bg-surface-card rounded-2xl border border-surface-border p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">CURRENT KIOSK CONFIGURATION</p>
            <p class="text-sm font-bold text-content-heading uppercase">
                {{ $setting?->terminal_name ? strtoupper($setting->terminal_name) : 'NOT YET CONFIGURED' }}
            </p>
            <p class="text-xs text-content-body">
                LAST UPDATED BY:
                <span class="font-semibold text-content-heading">
                    {{ $setting?->updatedBy ? Str::title(trim("{$setting->updatedBy->first_name} {$setting->updatedBy->middle_name} {$setting->updatedBy->last_name}")) : 'N/A' }}
                </span>
            </p>
        </div>

        <div>
            @if($setting && $setting->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-brand-tint-light text-brand-primary uppercase">
                    ACTIVE
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-brand-secondary/15 text-brand-secondary uppercase">
                    INACTIVE
                </span>
            @endif
        </div>
    </section>

    <!-- PIN Setup Form -->
    <div class="bg-surface-card rounded-2xl border border-surface-border p-5 sm:p-6 shadow-2xs max-w-xl">
        <form wire:submit="saveKioskSettings" class="space-y-4">
            <div>
                <label for="terminal_name" class="block text-xs font-semibold text-content-heading mb-1">
                    Terminal Name <span class="text-brand-secondary">*</span>
                </label>
                <input
                    id="terminal_name"
                    type="text"
                    wire:model="terminal_name"
                    placeholder="e.g. Main Library Entrance Kiosk"
                    class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                @error('terminal_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="kiosk_pin" class="block text-xs font-semibold text-content-heading mb-1">
                    New Kiosk Access PIN <span class="text-brand-secondary">*</span>
                </label>
                <input
                    id="kiosk_pin"
                    type="password"
                    wire:model="pin"
                    placeholder="Enter new PIN (min 4 characters)"
                    class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-mono tracking-widest text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                @error('pin') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="kiosk_pin_confirmation" class="block text-xs font-semibold text-content-heading mb-1">
                    Confirm Kiosk Access PIN <span class="text-brand-secondary">*</span>
                </label>
                <input
                    id="kiosk_pin_confirmation"
                    type="password"
                    wire:model="pin_confirmation"
                    placeholder="Re-enter new PIN"
                    class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-mono tracking-widest text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                @error('pin_confirmation') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-2.5 pt-1">
                <input
                    id="kiosk_is_active"
                    type="checkbox"
                    wire:model="is_active"
                    class="w-4 h-4 rounded border-surface-border text-brand-primary focus:ring-brand-primary cursor-pointer"
                >
                <label for="kiosk_is_active" class="text-xs font-semibold text-content-heading cursor-pointer">
                    Kiosk Terminal Enabled
                </label>
            </div>

            <div class="pt-3 border-t border-surface-border/70 flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveKioskSettings"
                    class="px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold flex items-center gap-2 cursor-pointer disabled:opacity-50 transition-colors shadow-2xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                >
                    <span wire:loading wire:target="saveKioskSettings" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>Save Kiosk PIN</span>
                </button>
            </div>
        </form>
    </div>
</div>
