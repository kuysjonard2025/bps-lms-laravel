<div class="min-h-screen flex flex-col items-center justify-center bg-surface-canvas p-4">
    <div class="w-full max-w-md space-y-6">

        {{-- Main Login Card --}}
        <section aria-label="Library borrower portal login" class="bg-surface-card rounded-2xl border border-surface-border shadow-xl p-6 sm:p-8 space-y-6">
            <div class="text-center space-y-2.5">
                <div class="w-20 h-20 bg-brand-tint-light text-brand-primary border border-brand-primary/25 rounded-2xl flex items-center justify-center mx-auto font-bold text-xl overflow-hidden p-2 shadow-2xs">
                    <img
                        src="{{ asset('images/bps-logo.png') }}"
                        alt="Bicutan Parochial School Library Logo"
                        class="w-16 h-16 object-contain"
                        onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-black text-content-heading text-xl tracking-tighter\'>BPS</span>';"
                    >
                </div>
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-bold text-content-heading tracking-tight">Library Borrower Portal</h1>
                    <p class="text-xs text-content-body leading-relaxed">
                        Enter your Student / Employee ID or scan your RFID card to view your loan history.
                    </p>
                </div>
            </div>

            <form wire:submit="login" class="space-y-4" novalidate>
                <div class="space-y-1.5">
                    <label for="borrowerNumber" class="block text-xs font-semibold text-content-heading uppercase tracking-wider">
                        Student / Employee ID or RFID <span class="text-brand-secondary">*</span>
                    </label>

                    <div class="relative">
                        <input
                            type="text"
                            id="borrowerNumber"
                            wire:model.trim="borrowerNumber"
                            placeholder="e.g. 2024-0001 or Scan RFID card"
                            autocomplete="username"
                            @class([
                                'w-full pl-4 py-3 text-xs sm:text-sm font-mono rounded-xl border bg-surface-canvas text-content-heading placeholder:text-content-muted transition outline-none focus:bg-surface-card focus:ring-4',
                                'pr-10' => filled($borrowerNumber),
                                'pr-4' => blank($borrowerNumber),
                                'border-brand-secondary focus:border-brand-secondary focus:ring-brand-secondary/15' => $errors->has('borrowerNumber'),
                                'border-surface-border focus:border-brand-primary focus:ring-brand-primary/15' => ! $errors->has('borrowerNumber'),
                            ])
                            autofocus
                            required
                        >

                        @if (filled($borrowerNumber))
                            <button
                                type="button"
                                wire:click="clearBorrowerNumber"
                                aria-label="Clear ID or RFID input"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-content-muted hover:text-content-heading transition cursor-pointer"
                            >
                                <x-heroicon-o-x-mark class="w-4 h-4" aria-hidden="true" />
                            </button>
                        @endif
                    </div>

                    @error('borrowerNumber')
                        <p role="alert" class="text-xs text-brand-secondary mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="login"
                    class="w-full py-3 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl font-semibold transition text-xs sm:text-sm shadow-2xs cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                >
                    <span wire:loading wire:target="login" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span wire:loading.remove wire:target="login">Login to Portal</span>
                    <span wire:loading wire:target="login">Authenticating...</span>
                </button>
            </form>
        </section>

        {{-- Footer --}}
        <footer class="text-center text-[11px] text-content-muted">
            &copy; {{ date('Y') }} Bicutan Parochial School, Inc. All rights reserved.
        </footer>

    </div>
</div>
