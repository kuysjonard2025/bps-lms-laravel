{{-- resources/views/components/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
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

    {{-- Disable asset preload tags to prevent console warnings --}}
    {{ Vite::usePreloadTagAttributes(false) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-dvh bg-surface-canvas text-content-heading font-sans antialiased selection:bg-brand-primary selection:text-white flex flex-col justify-center relative">

    {{-- Subtle Dot Background Pattern --}}
    <div
        aria-hidden="true"
        class="fixed inset-0 bg-[radial-gradient(var(--color-surface-border)_1.5px,transparent_1.5px)] bg-size-[18px_18px] mask-[radial-gradient(ellipse_60%_60%_at_50%_50%,#000_72%,transparent_100%)] pointer-events-none"
    ></div>

    {{-- Main Slot --}}
    <main class="relative z-10 w-full py-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
