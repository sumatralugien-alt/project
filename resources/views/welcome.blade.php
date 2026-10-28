<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Eventive') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 font-[DM_Sans] text-slate-900 antialiased">
        <div class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_78%_16%,_rgba(45,212,191,0.18),_transparent_25%),linear-gradient(135deg,_#f8fafc_0%,_#ecfeff_52%,_#e0f2fe_100%)]">
            <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 py-6 sm:px-8 lg:px-10">
                <a href="/" class="flex items-center gap-3 text-lg font-semibold tracking-tight text-slate-950"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 font-[Space_Grotesk] text-sm text-teal-300 shadow-lg shadow-slate-300/40">EV</span>Eventive</a>
                <nav class="flex items-center gap-3 text-sm font-semibold">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-slate-950 px-4 py-2.5 text-white transition hover:bg-slate-800">Dashboard <span aria-hidden="true"></span></a>
                    @else
                        <a href="{{ route('login') }}" class="hidden px-3 py-2 text-slate-600 transition hover:text-slate-950 sm:inline">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-slate-950 px-4 py-2.5 text-white transition hover:bg-slate-800">Get started <span aria-hidden="true"></span></a>
                    @endauth
                </nav>
            </header>

            <main class="relative mx-auto max-w-7xl px-5 pb-12 pt-10 sm:px-8 sm:pt-16 lg:px-10 lg:pb-20 lg:pt-20">
                <div class="grid items-center gap-14 lg:grid-cols-[0.94fr_1.06fr] lg:gap-16">
                    <section class="max-w-xl">
                        <p class="mb-6 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.24em] text-teal-700"><span class="h-px w-8 bg-teal-500"></span>Events, made human</p>
                        <h1 class="font-[Space_Grotesk] text-5xl font-semibold leading-[1.02] tracking-tight text-slate-950 sm:text-6xl lg:text-7xl">Make room for the moments that matter.</h1>
                        <p class="mt-7 max-w-lg text-lg leading-8 text-slate-600">Eventive gives clear place to plan, discover, and show up for what is next.</p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center"><a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-teal-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-teal-600/20 transition hover:-translate-y-0.5 hover:bg-teal-500">Create your workspace <span class="ml-2" aria-hidden="true"></span></a><a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white/60 px-6 py-3.5 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-white">Sign in to Eventive</a></div>
                        <div class="mt-12 flex flex-wrap gap-x-7 gap-y-3 text-sm font-medium text-slate-500"><span class="flex items-center gap-2"><span class="text-teal-600">✦</span>Simple event planning</span><span class="flex items-center gap-2"><span class="text-teal-600">✦</span>Live registrations</span></div>
                    </section>

                    <section class="relative lg:pl-6" aria-label="Event photo slideshow">
                        <div class="absolute -right-8 -top-10 h-40 w-40 rounded-full border border-teal-300/40 bg-teal-200/20 blur-2xl"></div>

                        <div class="relative max-w-xl rounded-[2rem] bg-white/40 p-3 shadow-2xl shadow-slate-900/15 ring-1 ring-slate-200/70 backdrop-blur-sm sm:p-4">
                            <div class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-100">
                                <img
                                    id="heroImage"
                                    src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80"
                                    alt="Students gathering at a campus event"
                                    class="h-[420px] w-full object-cover transition duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-6">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-teal-300">Eventive</p>
                                    <h2 class="mt-2 text-2xl font-semibold text-white">Campus moments, all in one place</h2>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-teal-400 shadow-[0_0_0_5px_rgba(45,212,191,0.18)]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                            </div>
                        </div>
                    </section>
                </div>
            </main>

            <footer class="mx-auto flex max-w-7xl flex-col gap-3 border-t border-slate-200/80 px-5 py-6 text-xs font-medium text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10"><span>One place for every next thing.</span><span>Developed By: Lugien Sumatra</span></footer>
        </div>

        <script>
            const heroImage = document.getElementById('heroImage');
            const slides = [
                {
                    src: 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                    alt: 'Students gathering at a campus event',
                },
                {
                    src: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
                    alt: 'Students networking at an event',
                },
                {
                    src: 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
                    alt: 'People cheering at a live event',
                },
            ];

            const dots = document.querySelectorAll('.mt-4 span');
            let currentSlide = 0;

            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;

                if (!heroImage) {
                    return;
                }

                heroImage.src = slides[currentSlide].src;
                heroImage.alt = slides[currentSlide].alt;

                dots.forEach((dot, index) => {
                    const active = index === currentSlide;
                    dot.classList.toggle('bg-teal-400', active);
                    dot.classList.toggle('shadow-[0_0_0_5px_rgba(45,212,191,0.18)]', active);
                    dot.classList.toggle('bg-slate-300', !active);
                    dot.classList.remove('shadow-[0_0_0_5px_rgba(45,212,191,0.18)]');
                });
            }, 3000);
        </script>
    </body>
</html>