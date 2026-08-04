<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
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
    class="bg-gray-100 font-sans antialiased text-gray-900 min-h-dvh flex flex-col selection:bg-blue-500 selection:text-white"
>

    {{-- Top Header Bar --}}
    <livewire:components.header />

    {{-- App Body Wrapper --}}
    <div class="flex flex-1 w-full relative">

        {{-- Navigation Sidebar --}}
        <livewire:components.sidebar />

        {{-- Main Page Content Area --}}
        <main class="flex-1 w-full p-3 sm:p-5 lg:p-8 min-w-0 flex flex-col justify-between">
            <div class="w-full max-w-[1600px] mx-auto flex-1">
                {{ $slot }}
            </div>
        </main>

    </div>

    {{-- Footer Bar --}}
    <livewire:components.footer />

    @livewireScripts
</body>
</html>
