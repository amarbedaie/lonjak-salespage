@props(['title' => null, 'forceLight' => false])
<!DOCTYPE html>
<html lang="ms" class="h-full antialiased" data-theme>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · Mendap' : 'Mendap — Platform Salespage Untuk Usahawan Malaysia' }}</title>
    <script>
        (function () {
            @if ($forceLight)
                document.documentElement.classList.remove('dark');
            @else
                try {
                    var t = localStorage.getItem('mendap-theme');
                    var m = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (t === 'dark' || (!t && m)) document.documentElement.classList.add('dark');
                } catch (e) {}
            @endif
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')
</body>
</html>
