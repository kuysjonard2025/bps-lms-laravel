<!-- resources/views/components/layouts/kiosk.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Patron RFID Kiosk' }}</title>

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-surface-canvas text-content-heading antialiased">
    <div
        x-data="{
            toasts: [],
            addToast(detail) {
                const payload = Array.isArray(detail) ? detail[0] : detail;
                const id = Date.now() + Math.random();
                this.toasts.push({
                    id,
                    type: payload?.type ?? 'info',
                    message: payload?.message ?? ''
                });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 4000);
            }
        }"
        @toast.window="addToast($event.detail)"
        class="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none"
        aria-live="polite"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                class="pointer-events-auto rounded-2xl border px-4 py-3 text-xs font-semibold uppercase shadow-lg bg-surface-card"
                :class="toast.type === 'error'
                    ? 'border-brand-secondary text-brand-secondary'
                    : 'border-brand-primary text-content-heading'"
            >
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <main class="h-full min-h-screen">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
