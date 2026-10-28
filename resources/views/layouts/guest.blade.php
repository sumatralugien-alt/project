<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Eventive') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 font-[DM_Sans] text-slate-900 antialiased">
        <div class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_right,_rgba(45,212,191,0.16),_transparent_34%),linear-gradient(135deg,_#f8fafc_0%,_#ecfeff_48%,_#e0f2fe_100%)]">
            <div class="mx-auto grid min-h-screen max-w-[1500px] lg:grid-cols-[0.82fr_1.18fr]">
                <aside class="relative hidden overflow-hidden bg-slate-950 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between xl:px-16">
                    <div class="absolute -right-32 top-24 h-80 w-80 rounded-full bg-teal-400/20 blur-3xl"></div>
                    <div class="absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"></div>
                    <a href="/" class="relative flex items-center gap-3 text-lg font-semibold tracking-tight">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-cyan-400 font-[Space_Grotesk] text-sm text-slate-950">EV</span>
                        Eventive
                    </a>
                    <div class="relative max-w-md">
                        <p class="mb-5 text-xs font-semibold uppercase tracking-[0.24em] text-teal-300">The event command center</p>
                        <h1 class="font-[Space_Grotesk] text-4xl font-semibold leading-tight xl:text-5xl">Bring every campus moment into focus.</h1>
                        <p class="mt-6 max-w-sm text-base leading-7 text-slate-400">One calm workspace for planning events, welcoming students, and keeping every registration moving.</p>
                        <div class="mt-10 flex items-center gap-3 text-sm text-slate-300"><span class="h-2 w-2 rounded-full bg-teal-400 shadow-[0_0_0_5px_rgba(45,212,191,0.12)]"></span>Built for people who make things happen</div>
                    </div>
                    <p class="relative text-xs text-slate-500">Eventive workspace · {{ now()->format('Y') }}</p>
                </aside>
                <main class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-16 lg:py-10">
                    <div class="flex items-center justify-between lg:justify-end">
                        <a href="/" class="flex items-center gap-2 text-base font-semibold tracking-tight text-slate-900 lg:hidden"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-950 font-[Space_Grotesk] text-xs text-teal-300">EV</span>Eventive</a>
                        <a href="/" class="text-sm font-semibold text-slate-500 transition hover:text-slate-900">Back to home</a>
                    </div>
                    <div class="flex flex-1 items-center justify-center py-10"><div class="w-full max-w-lg">{{ $slot }}</div></div>
                </main>
            </div>
        </div>
    </body>
</html>
