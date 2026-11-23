{{-- resources/views/livewire/auth/forgot-password.blade.php --}}
<div class="w-full max-w-md mx-auto px-4 py-8 space-y-6">

    {{-- Branding --}}
    <div class="text-center">
        <div class="mx-auto w-24 h-24 sm:w-28 sm:h-28 flex items-center justify-center p-2 mb-2 rounded-2xl bg-surface-card border border-surface-border shadow-2xs">
            <img
                src="{{ asset('images/bps-logo.png') }}"
                alt="BPS Logo"
                class="w-full h-full object-contain select-none pointer-events-none"
                onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-extrabold text-brand-primary text-2xl tracking-tight\'>BPS</span>';"
            >
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-content-heading tracking-tight">
            Bicutan Parochial School, Inc.
        </h1>
        <p class="text-xs font-medium text-content-body mt-0.5">
            Library Management System
        </p>
    </div>

    {{-- Card Container --}}
    <div class="bg-surface-card p-6 sm:p-8 rounded-2xl shadow-2xs border border-surface-border space-y-5">
        <div class="border-b border-surface-border/70 pb-3.5">
            <h2 class="text-base font-bold text-content-heading">Forgot your password?</h2>
            <p class="text-xs text-content-body mt-0.5">Enter your email address and we'll send you a password reset link.</p>
        </div>

        @error('warning')
            <div class="p-3 bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary text-xs rounded-xl flex items-center gap-2.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-brand-secondary" aria-hidden="true" />
                <span class="font-medium">{{ $message }}</span>
            </div>
        @enderror

        {{-- Form --}}
        <form wire:submit="sendResetLink" class="space-y-4">
            <div>
                <label for="fp_email" class="block text-xs font-semibold text-content-heading mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-envelope class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="email"
                        type="email"
                        id="fp_email"
                        autocomplete="email"
                        placeholder="admin@example.com"
                        required
                        autofocus
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('email') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                </div>
                @error('email')
                    <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="sendResetLink"
                class="w-full bg-brand-primary hover:bg-brand-primary-hover active:bg-brand-primary-hover text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-2xs transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
            >
                <span wire:loading.remove wire:target="sendResetLink">Send Password Reset Link</span>
                <span wire:loading wire:target="sendResetLink">Sending link...</span>
            </button>
        </form>

        <div class="text-center pt-2 border-t border-surface-border/70">
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="text-xs font-semibold text-brand-primary hover:text-brand-primary-hover transition inline-flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-md"
            >
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" aria-hidden="true" />
                <span>Back to login</span>
            </a>
        </div>
    </div>

</div>
