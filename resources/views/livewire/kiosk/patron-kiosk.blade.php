{{-- resources/views/livewire/kiosk/patron-kiosk.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div
    class="flex flex-col items-center justify-center min-h-screen bg-surface-canvas text-content-heading p-6 relative select-none"
    x-data="{
        timer: null,
        rfidBuffer: '',
        maxBufferLength: 50,
        displayDurationMs: 10000,

        handleGlobalKey(e) {
            if (document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                return;
            }

            if (e.key === 'Enter') {
                const code = this.rfidBuffer.trim();
                this.rfidBuffer = '';
                if (code.length > 0) {
                    $wire.scanRfid(code);
                }
            } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (this.rfidBuffer.length < this.maxBufferLength) {
                    this.rfidBuffer += e.key;
                } else {
                    this.rfidBuffer = e.key;
                }
            }
        },

        startAutoDismiss() {
            this.clearDismissTimer();

            this.timer = setTimeout(() => {
                this.timer = null;
                $wire.closeResultModal();
            }, this.displayDurationMs);
        },

        clearDismissTimer() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        }
    }"
    x-on:livewire:navigating.window="clearDismissTimer()"
    @window.keydown="handleGlobalKey($event)"
    @patron-scanned.window="startAutoDismiss()"
>
    <!-- Exit Terminal Button -->
    <div class="absolute top-6 right-6">
        <button
            type="button"
            wire:click="exitKiosk"
            wire:loading.attr="disabled"
            wire:target="exitKiosk"
            @click="clearDismissTimer()"
            class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-content-heading bg-surface-card hover:bg-brand-tint-light border border-surface-border shadow-2xs rounded-xl transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
        >
            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
            <span>Exit Terminal</span>
        </button>
    </div>

    <!-- Main Scanner View -->
    <section aria-label="Patron RFID scanner" class="w-full max-w-xl bg-surface-card border border-surface-border p-8 sm:p-12 rounded-3xl shadow-xl text-center flex flex-col items-center relative overflow-hidden">
        <!-- Top Accent Line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-brand-primary"></div>

        <!-- Operating Hours Badge -->
        <div class="mb-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-tint-light border border-brand-primary/25 text-brand-primary text-xs font-semibold uppercase">
            <x-heroicon-o-clock class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
            <span>ACTIVE HOURS: 7:00 AM – 5:00 PM</span>
        </div>

        <!-- Animated Scanner Icon -->
        <div class="mb-8 p-6 bg-brand-tint-light border border-brand-primary/25 rounded-full shadow-inner">
            <x-heroicon-o-identification class="w-16 h-16 sm:w-20 sm:h-20 text-brand-primary animate-pulse" aria-hidden="true" />
        </div>

        <h1 class="text-2xl sm:text-4xl font-extrabold text-content-heading tracking-tight mb-3">Please Tap Your Card</h1>
        <p class="text-content-body text-sm sm:text-base mb-6">Hold your ID badge near the scanner to log in or out.</p>

        <!-- Manual Input / Testing Box -->
        <div class="w-full max-w-sm mt-2">
            <form wire:submit="scanRfid" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <label for="kiosk-rfid-input" class="sr-only">School ID or RFID number</label>
                    <input
                        id="kiosk-rfid-input"
                        type="text"
                        wire:model="rfid_number"
                        wire:loading.attr="disabled"
                        wire:target="scanRfid"
                        maxlength="50"
                        placeholder="Type School ID / RFID & hit Enter..."
                        class="w-full px-3.5 py-2.5 text-xs font-mono bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl text-center uppercase focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                        autofocus
                    >
                </div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="scanRfid"
                    class="px-3.5 py-2.5 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50 shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                >
                    <span wire:loading.remove wire:target="scanRfid">Log</span>
                    <span wire:loading wire:target="scanRfid">...</span>
                </button>
            </form>
        </div>

        <!-- Auto-Dismissing Error Alert -->
        @if (session()->has('kiosk_error'))
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => { visible = false; }, 3500)"
                x-show="visible"
                x-transition.opacity
                role="alert"
                class="mt-6 w-full p-4 bg-brand-secondary/15 border border-brand-secondary/35 text-brand-secondary rounded-2xl text-xs sm:text-sm font-semibold uppercase"
            >
                {{ strtoupper(session('kiosk_error')) }}
            </div>
        @endif
    </section>

    <!-- Patron Tap Details Overlay Popup -->
    @if ($showResultModal && $scannedPatron)
        @php
            $middleInitial = $scannedPatron->middle_name ? mb_substr(trim($scannedPatron->middle_name), 0, 1) . '.' : '';
            $baseName = trim("{$scannedPatron->first_name} {$middleInitial} {$scannedPatron->last_name}");
            $patronFullName = Str::title($baseName) . ($scannedPatron->suffix ? ' ' . strtoupper(trim($scannedPatron->suffix)) : '');
        @endphp
        <div
            wire:key="scanned-patron-{{ $scanSequence }}"
            role="dialog"
            aria-modal="true"
            aria-labelledby="scanned-patron-name"
            class="fixed inset-0 z-50 flex items-center justify-center bg-content-heading/60 backdrop-blur-md p-6"
        >
            <div class="bg-surface-card border border-surface-border rounded-3xl p-8 sm:p-10 max-w-lg w-full text-center shadow-2xl relative overflow-hidden">

                <!-- Status Badge -->
                <div class="inline-block px-6 py-2 rounded-full text-xs font-black tracking-widest uppercase mb-6 {{ $actionStatus === 'LOGGED IN' ? 'bg-brand-tint-light text-brand-primary border border-brand-primary/30' : 'bg-brand-secondary/15 text-brand-secondary border border-brand-secondary/30' }}">
                    SUCCESSFULLY {{ strtoupper($actionStatus) }}
                </div>

                <!-- Patron Name -->
                <h2 id="scanned-patron-name" class="text-2xl sm:text-3xl font-extrabold text-content-heading mb-1">
                    {{ $patronFullName }}
                </h2>
                <p class="text-sm sm:text-base text-brand-primary font-semibold mb-8 uppercase">
                    {{ $scannedPatron->patronType?->name ? strtoupper($scannedPatron->patronType->name) : 'PATRON' }}
                </p>

                <!-- Patron Information Details Card -->
                <div class="bg-surface-canvas rounded-2xl p-6 border border-surface-border space-y-3 text-left text-xs sm:text-sm mb-4">
                    <div class="flex justify-between gap-4 border-b border-surface-border/70 pb-2">
                        <span class="text-content-muted">School ID:</span>
                        <span class="font-mono font-bold text-content-heading uppercase">{{ strtoupper($scannedPatron->school_id) }}</span>
                    </div>
                    @if ($scannedPatron->department)
                        <div class="flex justify-between gap-4 border-b border-surface-border/70 pb-2">
                            <span class="text-content-muted">Department:</span>
                            <span class="font-semibold text-content-heading uppercase text-right">{{ strtoupper($scannedPatron->department->name) }}</span>
                        </div>
                    @endif
                    @if ($scannedPatron->gradeLevel)
                        <div class="flex justify-between gap-4 border-b border-surface-border/70 pb-2">
                            <span class="text-content-muted">Grade Level:</span>
                            <span class="font-semibold text-content-heading text-right">{{ Str::title($scannedPatron->gradeLevel->name) }}</span>
                        </div>
                    @endif
                    @if ($scannedPatron->section)
                        <div class="flex justify-between gap-4 border-b border-surface-border/70 pb-2">
                            <span class="text-content-muted">Section:</span>
                            <span class="font-semibold text-content-heading text-right">{{ Str::title($scannedPatron->section->name) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <span class="text-content-muted">Timestamp:</span>
                        <span class="font-bold font-mono text-brand-primary uppercase text-right">{{ strtoupper($actionTime) }}</span>
                    </div>
                </div>

                <div class="text-xs text-content-muted font-medium animate-pulse mt-4">
                    Next patron can scan immediately to continue...
                </div>
            </div>
        </div>
    @endif
</div>
