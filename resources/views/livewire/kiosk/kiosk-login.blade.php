{{-- resources/views/livewire/kiosk/kiosk-login.blade.php --}}
<div
    x-data="{ showPassword: false, showPin: false }"
    class="flex flex-col items-center justify-center min-h-screen bg-surface-canvas p-4 sm:p-6 relative select-none"
>
    <!-- Top-Right Navigation to Main Route -->
    <div class="w-full max-w-sm flex justify-end mb-3 sm:mb-0 sm:w-auto sm:fixed sm:top-6 sm:right-6 z-10">
        <a
            href="{{ route('login') }}"
            wire:navigate
            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-content-heading bg-surface-card hover:bg-brand-tint-light border border-surface-border shadow-2xs rounded-xl transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
        >
            <x-heroicon-o-home class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
            <span>Main Home</span>
        </a>
    </div>

    <div class="w-full max-w-sm bg-surface-card border border-surface-border rounded-3xl p-6 sm:p-8 shadow-xl text-center">
        <div class="mb-6 p-4 bg-brand-tint-light border border-brand-primary/25 rounded-2xl inline-flex items-center justify-center">
            <x-heroicon-o-lock-closed class="w-10 h-10 text-brand-primary" aria-hidden="true" />
        </div>

        <h2 class="text-xl sm:text-2xl font-extrabold text-content-heading tracking-tight mb-1">Patron Logs Kiosk</h2>
        <p class="text-xs text-content-body mb-6">Sign in with a Librarian or Assistant account and enter the Kiosk PIN.</p>

        <form wire:submit="authenticate" class="space-y-4 text-left">
            <div>
                <label for="kiosk-login-username" class="block text-xs font-semibold text-content-heading mb-1">
                    Username Or Email <span class="text-brand-secondary">*</span>
                </label>
                <input
                    id="kiosk-login-username"
                    type="text"
                    wire:model="login"
                    placeholder="Enter username or email..."
                    autocomplete="username"
                    class="w-full px-4 py-2.5 bg-surface-canvas border border-surface-border rounded-xl text-xs sm:text-sm text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                    autofocus
                >
                @error('login')
                    <span class="text-brand-secondary text-xs block mt-1.5 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="kiosk-login-password" class="block text-xs font-semibold text-content-heading mb-1">
                    Account Password <span class="text-brand-secondary">*</span>
                </label>
                <div class="relative">
                    <input
                        id="kiosk-login-password"
                        :type="showPassword ? 'text' : 'password'"
                        wire:model="password"
                        placeholder="Enter account password..."
                        autocomplete="current-password"
                        class="w-full pl-4 pr-10 py-2.5 bg-surface-canvas border border-surface-border rounded-xl text-xs sm:text-sm text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide account password' : 'Show account password'"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-content-muted hover:text-content-heading cursor-pointer rounded-r-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        <x-heroicon-o-eye x-show="!showPassword" class="w-4 h-4" aria-hidden="true" />
                        <x-heroicon-o-eye-slash x-show="showPassword" x-cloak class="w-4 h-4" aria-hidden="true" />
                    </button>
                </div>
                @error('password')
                    <span class="text-brand-secondary text-xs block mt-1.5 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="kiosk-login-pin" class="block text-xs font-semibold text-content-heading mb-1">
                    Kiosk Access PIN <span class="text-brand-secondary">*</span>
                </label>
                <div class="relative">
                    <input
                        id="kiosk-login-pin"
                        :type="showPin ? 'text' : 'password'"
                        wire:model="pin"
                        placeholder="Enter kiosk PIN..."
                        inputmode="numeric"
                        class="w-full px-10 py-3 bg-surface-canvas border border-surface-border rounded-xl text-center text-base sm:text-lg font-mono tracking-widest text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                    >
                    <button
                        type="button"
                        @click="showPin = !showPin"
                        :aria-label="showPin ? 'Hide kiosk access PIN' : 'Show kiosk access PIN'"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-content-muted hover:text-content-heading cursor-pointer rounded-r-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        <x-heroicon-o-eye x-show="!showPin" class="w-4 h-4" aria-hidden="true" />
                        <x-heroicon-o-eye-slash x-show="showPin" x-cloak class="w-4 h-4" aria-hidden="true" />
                    </button>
                </div>
                @error('pin')
                    <span class="text-brand-secondary text-xs block mt-1.5 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="authenticate"
                class="w-full py-3 bg-brand-primary hover:bg-brand-primary-hover text-white font-semibold rounded-xl transition shadow-2xs text-xs sm:text-sm cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <span wire:loading wire:target="authenticate" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                <span wire:loading.remove wire:target="authenticate">Unlock Terminal</span>
                <span wire:loading wire:target="authenticate">Unlocking...</span>
            </button>
        </form>
    </div>
</div>
