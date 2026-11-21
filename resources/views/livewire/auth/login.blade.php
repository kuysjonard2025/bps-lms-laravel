{{-- resources/views/livewire/auth/login.blade.php --}}
<div class="w-full max-w-md mx-auto px-4 py-8 space-y-6">

    {{-- Header / Branding --}}
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

    {{-- Main Login Card --}}
    <div class="bg-surface-card p-6 sm:p-8 rounded-2xl shadow-2xs border border-surface-border space-y-5">
        <div class="border-b border-surface-border/70 pb-3.5">
            <h2 class="text-base font-bold text-content-heading">Sign in to your account</h2>
            <p class="text-xs text-content-body mt-0.5">Enter your credentials to access the library workspace</p>
        </div>

        @if (session('status'))
            <div class="p-3 bg-brand-tint-light border border-brand-tint text-brand-primary text-xs font-medium rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        {{-- Alert Banner for Authentication Warning Error --}}
        @error('warning')
            <div class="p-3 bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary text-xs rounded-xl flex items-center gap-2.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-brand-secondary" aria-hidden="true" />
                <span class="font-medium">{{ $message }}</span>
            </div>
        @enderror

        {{-- Login Form --}}
        <form wire:submit="login" class="space-y-4">
            {{-- Username / Email Address --}}
            <div>
                <label for="login_umail" class="block text-xs font-semibold text-content-heading mb-1.5">
                    Username or Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-user class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="umail"
                        type="text"
                        id="login_umail"
                        autocomplete="username"
                        placeholder="username or admin@example.com"
                        required
                        autofocus
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('umail') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                </div>
                @error('umail')
                    <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password with Alpine.js Show/Hide Toggle --}}
            <div x-data="{ showPassword: false }">
                <label for="login_password" class="block text-xs font-semibold text-content-heading mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-content-muted" aria-hidden="true" />
                    </div>
                    <input
                        wire:model="password"
                        :type="showPassword ? 'text' : 'password'"
                        id="login_password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        required
                        class="w-full pl-9 pr-10 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('password') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
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

            {{-- Remember Me + Forgot Password --}}
            <div class="flex items-center justify-between gap-2 pt-1">
                <label for="login_remember" class="flex items-center text-xs text-content-body cursor-pointer select-none">
                    <input
                        id="login_remember"
                        wire:model="remember"
                        type="checkbox"
                        class="w-4 h-4 rounded border-surface-border text-brand-primary focus:ring-brand-primary/25 cursor-pointer"
                    >
                    <span class="ml-2 font-medium">Remember me</span>
                </label>

                <a
                    href="{{ Route::has('password.request') ? route('password.request') : '#' }}"
                    wire:navigate
                    class="text-xs font-semibold text-brand-primary hover:text-brand-primary-hover transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-md"
                >
                    Forgot password?
                </a>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="w-full bg-brand-primary hover:bg-brand-primary-hover active:bg-brand-primary-hover text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-2xs transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
            >
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login">Signing in...</span>
            </button>
        </form>
    </div>

    {{-- Visitor Quick Access --}}
    <div class="bg-surface-card p-4 rounded-2xl shadow-2xs border border-surface-border">
        <p class="text-[11px] font-semibold text-content-muted uppercase tracking-wider text-center mb-3">
            Visitor Access
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a
                href="{{ Route::has('kiosk.login') ? route('kiosk.login') : '#' }}"
                wire:navigate
                class="flex flex-col items-center justify-center gap-1.5 py-3 px-2.5 rounded-xl border border-surface-border bg-surface-canvas hover:bg-brand-tint-light hover:border-brand-primary/40 transition-colors group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-content-muted group-hover:text-brand-primary transition-colors" aria-hidden="true" />
                <span class="text-xs font-semibold text-content-heading group-hover:text-brand-primary transition-colors">Patron Time Logs</span>
            </a>

            <a
                href="{{ Route::has('patron.portal') ? route('patron.portal') : '#' }}"
                wire:navigate
                class="flex flex-col items-center justify-center gap-1.5 py-3 px-2.5 rounded-xl border border-surface-border bg-surface-canvas hover:bg-brand-tint-light hover:border-brand-primary/40 transition-colors group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <x-heroicon-o-identification class="w-5 h-5 text-content-muted group-hover:text-brand-primary transition-colors" aria-hidden="true" />
                <span class="text-xs font-semibold text-content-heading group-hover:text-brand-primary transition-colors">Patron Records Portal</span>
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-[11px] text-content-muted">
        &copy; {{ date('Y') }} Bicutan Parochial School, Inc. All rights reserved.
    </div>
</div>
