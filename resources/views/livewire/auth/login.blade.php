<div class="w-full max-w-md mx-auto px-4 space-y-6">

    {{-- Header / Branding --}}
    <div class="text-center">
        {{-- Cleaned-up Logo Container --}}
        <div class="mx-auto w-28 h-28 flex items-center justify-center p-1 mb-2">
            <img
                src="{{ asset('images/bps-logo.png') }}"
                alt="BPS Logo"
                class="w-full h-full object-contain select-none pointer-events-none drop-shadow-sm"
                onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-black text-blue-900 text-2xl tracking-tighter\'>BPS</span>';"
            >
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-blue-950 tracking-tight">Bicutan Parochial School, Inc.</h1>
        <p class="text-xs font-medium text-slate-500 mt-0.5">Library Management System</p>
    </div>

    {{-- Card Container --}}
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 space-y-5">
        <div class="border-b border-slate-100 pb-3.5">
            <h2 class="text-base font-bold text-slate-800">Sign in to your account</h2>
            <p class="text-xs text-slate-500">Please enter your credentials to continue</p>
        </div>

        {{-- Alert Banner for Authentication Warning Error --}}
        @error('warning')
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg flex items-center gap-2.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-rose-500" />
                <span class="font-medium">{{ $message }}</span>
            </div>
        @enderror

        {{-- Login Form --}}
        <form wire:submit="login" class="space-y-4">
            {{-- Username / Email Address --}}
            <div>
                <label for="umail" class="block text-xs font-semibold text-slate-700 mb-1">Username or Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-user class="w-4 h-4 text-slate-400" />
                    </div>
                    <input
                        wire:model="umail"
                        type="text"
                        id="umail"
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50/50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 @error('umail') border-rose-300 bg-rose-50/30 @enderror"
                        placeholder="username or admin@example.com"
                        required
                        autofocus
                    >
                </div>
                @error('umail')
                    <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password with Alpine.js Show/Hide Toggle --}}
            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-slate-400" />
                    </div>
                    <input
                        wire:model="password"
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        class="w-full pl-9 pr-10 py-2 text-xs bg-slate-50/50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 @error('password') border-rose-300 bg-rose-50/30 @enderror"
                        placeholder="••••••••"
                        required
                    >
                    {{-- Password Visibility Toggle --}}
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer"
                    >
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember Me + Forgot Password --}}
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center text-xs text-slate-600 cursor-pointer select-none">
                    <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="ml-2 font-medium">Remember me</span>
                </label>

                <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    Forgot password?
                </a>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm shadow-blue-500/30 transition duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer mt-2"
            >
                <span wire:loading.remove>Sign In</span>
                <span wire:loading class="inline-block">Signing in...</span>
            </button>
        </form>
    </div>

    {{-- Visitor Quick Access --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide text-center mb-3">
            Visitor Access
        </p>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ Route::has('kiosk.login') ? route('kiosk.login') : '#' }}" wire:navigate class="flex flex-col items-center justify-center gap-1.5 py-3 px-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-blue-50 hover:border-blue-200 transition-all group">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition" />
                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700">Patron Time Logs</span>
            </a>

            <a href="{{ Route::has('patron.portal') ? route('patron.portal') : '#' }}" wire:navigate class="flex flex-col items-center justify-center gap-1.5 py-3 px-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-blue-50 hover:border-blue-200 transition-all group">
                <x-heroicon-o-identification class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition" />
                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700">Patron Records Portal</span>
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-[11px] text-slate-400">
        &copy; {{ date('Y') }} Bicutan Parochial School, Inc. All rights reserved.
    </div>
</div>
