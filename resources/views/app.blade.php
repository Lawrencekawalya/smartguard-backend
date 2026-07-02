<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">
        <meta name="theme-color" content="#00C853">
        @php($appUrl = rtrim(config('app.url', url('/')), '/'))
        <meta name="description" content="SmartGuard protects and monitors electrical systems with voltage, current, power, energy, relay, and fault analytics.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="SmartGuard">
        <meta property="og:title" content="SmartGuard">
        <meta property="og:description" content="Electrical protection and energy monitoring for connected SmartGuard devices.">
        <meta property="og:url" content="{{ $appUrl }}">
        <meta property="og:image" content="{{ $appUrl }}/og-image.png?v=2">
        <meta property="og:image:secure_url" content="{{ $appUrl }}/og-image.png?v=2">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="SmartGuard">
        <meta name="twitter:description" content="Electrical protection and energy monitoring for connected SmartGuard devices.">
        <meta name="twitter:image" content="{{ $appUrl }}/og-image.png?v=2">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'SmartGuard') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
