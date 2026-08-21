<div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-sky-50 via-slate-100 to-sky-100/60 p-6 select-none">
    <div class="w-full max-w-sm bg-white border border-sky-100 rounded-3xl p-8 shadow-xl shadow-sky-900/5 text-center">
        <div class="mb-6 p-4 bg-sky-50 border border-sky-200/60 rounded-2xl inline-block">
            <svg class="w-10 h-10 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h2 class="text-2xl font-extrabold text-sky-950 mb-1">Kiosk Terminal</h2>
        <p class="text-xs text-slate-500 mb-6">Enter PIN to initialize terminal session.</p>

        <form wire:submit.prevent="authenticate" class="space-y-4">
            <div>
                <input
                    type="password"
                    wire:model="pin"
                    placeholder="Enter Access PIN..."
                    class="w-full px-4 py-3 bg-sky-50/50 border border-sky-200 rounded-xl text-center text-lg font-mono tracking-widest text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition"
                    autofocus
                >
                @error('pin')
                    <span class="text-red-500 text-xs block mt-2 font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-3 bg-sky-900 hover:bg-sky-800 text-white font-semibold rounded-xl transition shadow-md shadow-sky-900/20 text-sm"
            >
                Unlock Terminal
            </button>
        </form>
    </div>
</div>
