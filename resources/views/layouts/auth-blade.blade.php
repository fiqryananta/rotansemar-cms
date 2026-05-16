<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';
                if (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">

        @fonts
        @vite(['resources/css/app.css'])
        <title>{{ isset($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

        {{-- Plus Jakarta Sans --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
        <style>
            :root {
                --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif,
                    'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            }
            html, body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="min-h-screen antialiased">
        <div class="relative min-h-screen w-full overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-blue-300 opacity-20 blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-fuchsia-300 opacity-20 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 h-80 w-80 rounded-full bg-cyan-300 opacity-20 blur-3xl"></div>
            </div>

            <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
