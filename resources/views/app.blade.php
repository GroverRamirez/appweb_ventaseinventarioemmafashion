<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Force light theme as default. Only honor an explicit user preference for "dark". --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';
                const stored = (typeof localStorage !== 'undefined') ? localStorage.getItem('appearance') : null;

                // Migrate legacy "system" preference -> "light"
                if (stored === 'system' && typeof localStorage !== 'undefined') {
                    localStorage.setItem('appearance', 'light');
                }

                const effective = (stored && stored !== 'system') ? stored : appearance;

                document.documentElement.classList.toggle('dark', effective === 'dark');
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
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
