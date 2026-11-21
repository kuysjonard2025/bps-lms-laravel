{{-- resources/views/components/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" style="--header-h: 72px;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - BPS LMS' : 'Bicutan Parochial School, Inc.' }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Disable asset preload tags to prevent console warnings --}}
    {{ Vite::usePreloadTagAttributes(false) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- ApexCharts CDN loaded here so it's ready before Alpine/Livewire scripts execute --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body
    x-data="{ sidebarOpen: false }"
    :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }"
    class="bg-surface-canvas font-sans antialiased text-content-heading min-h-dvh flex flex-col selection:bg-brand-primary selection:text-white"
>

    {{-- Sticky Top Header Bar --}}
    <div class="sticky top-0 z-40 w-full">
        <livewire:components.header />
    </div>

    {{-- App Body Wrapper --}}
    <div class="flex flex-1 w-full min-w-0 relative">

        {{-- Desktop Layout Spacer for Fixed Sidebar (>= 1024px only) --}}
        <div class="hidden lg:block lg:w-64 shrink-0" aria-hidden="true"></div>

        {{-- Navigation Sidebar --}}
        <livewire:components.sidebar />

        {{-- Main Page Content Area --}}
        <main class="flex-1 w-full p-3 sm:p-5 lg:p-6 min-w-0 flex flex-col justify-between overflow-x-clip">
            <div class="w-full max-w-[1600px] mx-auto min-w-0 flex-1">
                {{ $slot }}
            </div>
        </main>

    </div>

    {{-- Footer Bar --}}
    <livewire:components.footer />

    @livewireScripts

    <script>
        (function syncHeaderHeight() {
            const updateHeight = () => {
                const header = document.querySelector('header');
                if (!header) return;
                const h = Math.round(header.getBoundingClientRect().height);
                if (h > 0) {
                    document.documentElement.style.setProperty('--header-h', `${h}px`);
                }
            };

            document.addEventListener('DOMContentLoaded', () => {
                updateHeight();
                const header = document.querySelector('header');
                if (header && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(updateHeight).observe(header);
                }
            });

            document.addEventListener('livewire:navigated', updateHeight);
            window.addEventListener('resize', updateHeight);
        })();
    </script>
</body>
</html>
