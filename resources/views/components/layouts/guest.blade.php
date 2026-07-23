<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ? $title . ' - BPS LMS' : 'BPS LMS' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900 min-h-screen flex items-center justify-center">
    <main class="w-full mx-auto p-6">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
