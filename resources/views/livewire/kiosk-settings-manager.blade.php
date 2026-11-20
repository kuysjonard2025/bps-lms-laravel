<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">Kiosk Terminal Settings</h1>
            <p class="mt-1 text-xs sm:text-sm text-gray-500">Configure And Rotate The Kiosk Access PIN Used To Unlock Patron Attendance Kiosks.</p>
        </div>
    </div>

    <!-- Current Status Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase text-gray-500">CURRENT KIOSK CONFIGURATION</p>
            <p class="text-sm font-bold text-gray-900 uppercase">
                {{ $setting?->terminal_name ? strtoupper($setting->terminal_name) : 'NOT YET CONFIGURED' }}
            </p>
            <p class="text-xs text-gray-500">
                LAST UPDATED BY:
                <span class="font-semibold text-gray-800">
                    {{ $setting?->updatedBy ? \Illuminate\Support\Str::title(trim("{$setting->updatedBy->first_name} {$setting->updatedBy->middle_name} {$setting->updatedBy->last_name}")) : 'N/A' }}
                </span>
            </p>
        </div>

        <div>
            @if($setting && $setting->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 uppercase">
                    ACTIVE
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 uppercase">
                    INACTIVE
                </span>
            @endif
        </div>
    </div>

    <!-- PIN Setup Form -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-xs max-w-xl">
        <form wire:submit="saveKioskSettings" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Terminal Name <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="terminal_name"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500"
                >
                @error('terminal_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">New Kiosk Access PIN <span class="text-rose-500">*</span></label>
                <input
                    type="password"
                    wire:model="pin"
                    placeholder="Enter new PIN (min 4 characters)"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono tracking-widest text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500"
                >
                @error('pin') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Confirm Kiosk Access PIN <span class="text-rose-500">*</span></label>
                <input
                    type="password"
                    wire:model="pin_confirmation"
                    placeholder="Re-enter new PIN"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono tracking-widest text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500"
                >
                @error('pin_confirmation') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input
                    id="kiosk_is_active"
                    type="checkbox"
                    wire:model="is_active"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                >
                <label for="kiosk_is_active" class="text-xs font-semibold text-gray-700 cursor-pointer">
                    Kiosk Terminal Enabled
                </label>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold flex items-center gap-2 cursor-pointer disabled:opacity-50"
                >
                    <span wire:loading wire:target="saveKioskSettings" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                    Save Kiosk PIN
                </button>
            </div>
        </form>
    </div>
</div>
