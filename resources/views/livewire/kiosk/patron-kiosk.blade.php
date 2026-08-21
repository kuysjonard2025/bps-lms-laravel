<div
    class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-sky-50 via-slate-100 to-sky-100/60 text-slate-800 p-6 relative select-none"
    x-data="{
        timer: null,
        rfidBuffer: '',

        handleGlobalKey(e) {
            if (document.activeElement.tagName === 'INPUT') return;

            if (e.key === 'Enter') {
                if (this.rfidBuffer.trim().length > 0) {
                    $wire.scanRfid(this.rfidBuffer);
                    this.rfidBuffer = '';
                }
            } else if (e.key.length === 1) {
                this.rfidBuffer += e.key;
            }
        },

        startAutoDismiss() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                $wire.closeResultModal();
            }, 4000);
        }
    }"
    @window.keydown="handleGlobalKey($event)"
    @patron-scanned.window="startAutoDismiss()"
>
    <!-- Exit Terminal Button -->
    <div class="absolute top-6 right-6">
        <button
            type="button"
            wire:click="exitKiosk"
            class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-sky-900 bg-white/80 hover:bg-white border border-sky-200/80 hover:border-sky-300 shadow-sm rounded-xl backdrop-blur-md transition-all duration-200 cursor-pointer"
        >
            <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Exit Terminal
        </button>
    </div>

    <!-- Main Scanner View -->
    <div class="w-full max-w-xl bg-white/90 backdrop-blur-md border border-sky-100 p-12 rounded-3xl shadow-xl shadow-sky-900/5 text-center flex flex-col items-center relative overflow-hidden">
        <!-- Top Navy Accent Line -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-sky-500 via-sky-700 to-sky-900"></div>

        <!-- Animated Light Blue Scanner Icon -->
        <div class="mb-8 p-6 bg-sky-50 border border-sky-200/60 rounded-full shadow-inner">
            <svg class="w-20 h-20 text-sky-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457-.39-2.823-1.07-4" />
            </svg>
        </div>

        <h1 class="text-4xl font-extrabold text-sky-950 tracking-tight mb-3">Please Tap Your Card</h1>
        <p class="text-slate-500 text-base mb-6">Hold your ID badge near the scanner to log in or out.</p>

        <!-- Development Testing Input Box -->
        <div class="w-full max-w-xs mt-2">
            <form wire:submit.prevent="scanRfid">
                <input
                    type="text"
                    wire:model="rfid_number"
                    placeholder="[Dev Test] Type Patron ID & hit Enter..."
                    class="w-full px-3 py-2 text-xs bg-sky-50/50 text-slate-700 border border-sky-200 rounded-xl text-center focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition"
                    autofocus
                >
            </form>
        </div>

        <!-- Auto-Dismissing Error Alert -->
        @if (session()->has('kiosk_error'))
            <div
                x-data
                x-init="setTimeout(() => { $el.remove(); }, 3500)"
                class="mt-6 w-full p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-sm font-semibold shadow-sm"
            >
                {{ session('kiosk_error') }}
            </div>
        @endif
    </div>

    <!-- Patron Tap Details Overlay Popup -->
    @if ($showResultModal && $scannedPatron)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-6">
            <div class="bg-white border border-sky-100 rounded-3xl p-10 max-w-lg w-full text-center shadow-2xl relative overflow-hidden">

                <!-- Status Badge -->
                <div class="inline-block px-6 py-2 rounded-full text-xs font-black tracking-widest uppercase mb-6 {{ $actionStatus === 'LOGGED IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    Successfully {{ $actionStatus }}
                </div>

                <!-- Patron Name -->
                <h2 class="text-3xl font-extrabold text-sky-950 mb-1">
                    {{ $scannedPatron->first_name }} {{ $scannedPatron->last_name }} {{ $scannedPatron->suffix }}
                </h2>
                <p class="text-base text-sky-600 font-semibold mb-8">
                    {{ $scannedPatron->patronType->name ?? 'Patron' }}
                </p>

                <!-- Patron Information Details Card -->
                <div class="bg-sky-50/60 rounded-2xl p-6 border border-sky-100 space-y-3 text-left text-sm mb-4">
                    <div class="flex justify-between border-b border-sky-200/50 pb-2">
                        <span class="text-slate-500">Patron ID:</span>
                        <span class="font-mono font-bold text-sky-950">{{ $scannedPatron->patron_id }}</span>
                    </div>
                    @if ($scannedPatron->gradeLevel)
                        <div class="flex justify-between border-b border-sky-200/50 pb-2">
                            <span class="text-slate-500">Grade Level:</span>
                            <span class="font-semibold text-slate-800">{{ $scannedPatron->gradeLevel->name ?? '-' }}</span>
                        </div>
                    @endif
                    @if ($scannedPatron->section)
                        <div class="flex justify-between border-b border-sky-200/50 pb-2">
                            <span class="text-slate-500">Section:</span>
                            <span class="font-semibold text-slate-800">{{ $scannedPatron->section->name ?? '-' }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-500">Timestamp:</span>
                        <span class="font-bold text-sky-700">{{ $actionTime }}</span>
                    </div>
                </div>

                <div class="text-xs text-sky-800/60 font-medium animate-pulse mt-4">
                    Next patron can scan immediately to continue...
                </div>
            </div>
        </div>
    @endif
</div>
