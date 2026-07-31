<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - BPS LMS' : 'Bicutan Parochial School, Inc.' }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- ApexCharts CDN loaded here so it's ready before Alpine/Livewire scripts execute --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body x-data="{ sidebarOpen: false }" class="bg-gray-100 font-sans antialiased text-gray-900 min-h-screen flex flex-col">

    {{-- Top Header Bar --}}
    <livewire:components.header />

    <div class="flex flex-1 relative">
        {{-- Navigation Sidebar --}}
        <livewire:components.sidebar />

        {{-- Main Page Content --}}
        <main class="flex-1 p-4 lg:p-6 min-w-0">
            {{ $slot }}
        </main>
    </div>

    {{-- Footer Bar --}}
    <livewire:components.footer />

    @livewireScripts
</body>
</html>
