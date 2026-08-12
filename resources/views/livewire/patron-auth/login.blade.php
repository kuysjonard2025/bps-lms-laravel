<div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200/80 shadow-xl p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-18 h-18 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mx-auto font-bold text-xl">
                <img src="{{ asset('images/bps-logo.png') }}" alt="Library Logo" class="w-15 h-15">
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Library Patron Portal</h1>
            <p class="text-xs text-slate-500">Enter your Patron ID to access OPAC search and view your loans.</p>
        </div>

        <form wire:submit="login" class="space-y-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-600 uppercase">Patron ID</label>
                <input
                    type="text"
                    wire:model="patronId"
                    placeholder="e.g. PAT-2024-001"
                    class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-800 transition"
                    autofocus
                >
                @error('patronId')
                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition text-sm shadow-sm">
                Login to Portal
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="text-center text-[11px] text-slate-400">
        &copy; {{ date('Y') }} Bicutan Parochial School, Inc. All rights reserved.
    </div>
</div>
