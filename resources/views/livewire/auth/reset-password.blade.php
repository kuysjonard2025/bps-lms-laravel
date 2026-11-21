{{-- resources/views/livewire/auth/reset-password.blade.php --}}
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
            <h2 class="text-base font-bold text-content-heading">Set a new password</h2>
            <p class="text-xs text-content-body mt-0.5">Please enter your email and your new password below.</p>
        </div>

        @error('warning')
            <div class="p-3 bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary text-xs rounded-xl flex items-center gap-2.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-brand-secondary" aria-hidden="true" />
                <span class="font-medium">{{ $message }}</span>
            </div>
        @enderror

        <form wire:submit="resetPassword" class="space-y-4">
            {{-- Email Address --}}
            <div>
                <label for="rp_email" class="block text-xs font-semibold text-content-heading mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-envelope class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="email"
                        type="email"
                        id="rp_email"
                        autocomplete="email"
                        placeholder="admin@example.com"
                        required
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('email') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                </div>
                @error('email')
                    <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- New Password --}}
            <div x-data="{ showPassword: false }">
                <label for="rp_password" class="block text-xs font-semibold text-content-heading mb-1.5">
                    New Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="password"
                        :type="showPassword ? 'text' : 'password'"
                        id="rp_password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        required
                        class="w-full pl-9 pr-10 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('password') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide new password' : 'Show new password'"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-content-muted hover:text-content-heading transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-r-xl"
                    >
                        <x-heroicon-o-eye x-show="!showPassword" class="w-4 h-4" aria-hidden="true" />
                        <x-heroicon-o-eye-slash x-show="showPassword" x-cloak class="w-4 h-4" aria-hidden="true" />
                    </button>
                </div>
                @error('password')
                    <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="rp_password_confirmation" class="block text-xs font-semibold text-content-heading mb-1.5">
                    Confirm New Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="password_confirmation"
                        type="password"
                        id="rp_password_confirmation"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        required
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                    >
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="resetPassword"
                class="w-full bg-brand-primary hover:bg-brand-primary-hover active:bg-brand-primary-hover text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-2xs transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
            >
                <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                <span wire:loading wire:target="resetPassword">Resetting password...</span>
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
