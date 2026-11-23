{{-- resources/views/components/global-toast.blade.php --}}
<div
    x-data="{
        toasts: [],
        addToast(detail) {
            const payload = Array.isArray(detail) ? (detail[0] ?? {}) : (detail ?? {});
            const message = String(payload.message ?? '').trim();
            if (!message) return;

            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                type: payload.type === 'error' ? 'error' : 'success',
                message,
            });

            setTimeout(() => {
                this.dismiss(id);
            }, 4000);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if (session()->has('toast_message'))
            addToast({
                message: @js(session('toast_message')),
                type: @js(session('toast_type', 'success'))
            });
        @elseif (session()->has('status'))
            addToast({
                message: @js(session('status')),
                type: 'success'
            });
        @elseif (session()->has('error'))
            addToast({
                message: @js(session('error')),
                type: 'error'
            });
        @endif
    "
    @toast.window="addToast($event.detail)"
    x-cloak
    aria-live="polite"
    aria-atomic="false"
    class="fixed top-4 right-4 z-[60] flex flex-col gap-2 max-w-sm w-[calc(100vw-2rem)] sm:w-auto pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            role="status"
            :class="toast.type === 'error'
                ? 'bg-brand-secondary text-white border-brand-secondary'
                : 'bg-surface-card text-content-heading border-surface-border'"
            class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl border shadow-lg text-xs font-medium transition-all duration-200"
        >
            <span
                :class="toast.type === 'error' ? 'bg-white/20 text-white' : 'bg-brand-tint-light text-brand-primary'"
                class="mt-0.5 w-5 h-5 rounded-full flex items-center justify-center shrink-0 text-[11px] font-bold"
                aria-hidden="true"
            >
                <template x-if="toast.type === 'error'"><span>!</span></template>
                <template x-if="toast.type !== 'error'"><span>&#10003;</span></template>
            </span>

            <p class="flex-1 leading-relaxed break-words" x-text="toast.message"></p>

            <button
                type="button"
                @click="dismiss(toast.id)"
                aria-label="Dismiss notification"
                :class="toast.type === 'error' ? 'text-white/80 hover:text-white' : 'text-content-muted hover:text-content-heading'"
                class="text-base leading-none font-bold cursor-pointer shrink-0 focus-visible:outline-none"
            >
                &times;
            </button>
        </div>
    </template>
</div>
