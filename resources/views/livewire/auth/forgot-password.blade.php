<div class="w-full max-w-md mx-auto px-4 space-y-6">

    {{-- Branding --}}
    <div class="text-center">
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
            <h2 class="text-base font-bold text-slate-800">Forgot your password?</h2>
            <p class="text-xs text-slate-500">Enter your email address and we'll send you a password reset link.</p>
        </div>

        {{-- Success Banner --}}
        @if ($status)
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg flex items-center gap-2.5">
                <x-heroicon-o-check-circle class="w-4 h-4 shrink-0 text-emerald-500" />
                <span class="font-medium">{{ $status }}</span>
            </div>
        @endif

        {{-- Form --}}
        <form wire:submit="sendResetLink" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-envelope class="w-4 h-4 text-slate-400" />
                    </div>
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50/50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 @error('email') border-rose-300 bg-rose-50/30 @enderror"
                        placeholder="admin@example.com"
                        required
                        autofocus
                    >
                </div>
                @error('email')
                    <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm shadow-blue-500/30 transition duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
            >
                <span wire:loading.remove>Send Password Reset Link</span>
                <span wire:loading class="inline-block">Sending link...</span>
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                &larr; Back to login
            </a>
        </div>
    </div>

</div>
