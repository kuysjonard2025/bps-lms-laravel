<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Patron RFID Kiosk' }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full text-white antialiased">
    <main class="h-full min-h-screen">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
