<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Student portal</p>
        <h2 class="text-2xl font-semibold text-slate-900">Good to see you, {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-teal-700 p-6 text-white shadow-lg shadow-slate-200">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-lg font-semibold ring-1 ring-white/10">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.20em] text-slate-300">Student account</p>
                        <h3 class="mt-1 text-2xl font-semibold">{{ auth()->user()->name }}</h3>
                    </div>
                </div>

                <div class="inline-flex items-center rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-teal-100">
                    Active
                </div>
            </div>
        </section>

        <x-weather-card :weather="$weather ?? null" />

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Active registrations</p>
                    <span class="rounded-full bg-emerald-100 p-2 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414L8.5 11.586l6.793-6.793a1 1 0 011.414 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $registrationCount }}</p>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Next event</p>
                    <span class="rounded-full bg-teal-100 p-2 text-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 2a1 1 0 011 1v1h6V3a1 1 0 112 0v1h.5A2.5 2.5 0 0118 6.5v8A2.5 2.5 0 0115.5 17h-11A2.5 2.5 0 012 14.5v-8A2.5 2.5 0 014.5 4H5V3a1 1 0 011-1zm-1 6h12v6.5A.5.5 0 0115.5 15h-11a.5.5 0 01-.5-.5V8z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-xl font-semibold text-slate-900">{{ $upcomingEvents->first()?->starts_at->format('M j') ?: 'None scheduled' }}</p>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">My schedule</p>
                    <span class="rounded-full bg-violet-100 p-2 text-violet-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 3a1 1 0 011-1h10a1 1 0 011 1v2h1a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2h1V3zm2 2h8V4H6v1zm-1 4h10v7H5V9z"/>
                        </svg>
                    </span>
                </div>
                <a href="{{ route('registrations.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-700 hover:text-teal-800">
                    View registrations
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 11-1.414-1.414L13.586 11H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Upcoming events</h3>
                <a class="text-sm font-semibold text-teal-700" href="{{ route('events.index') }}">Browse all</a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $event->starts_at->format('M j, Y') }}</p>
                            @php
                                $registeredCount = $event->registrations()->where('status', 'Registered')->count();
                                $isFull = $event->capacity !== null && $registeredCount >= $event->capacity;
                                $isOpen = $event->status === 'Published' && !$isFull && !$event->ends_at->isPast();
                            @endphp
                            @if ($isOpen)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Open</span>
                            @elseif ($isFull)
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Full</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-600">Closed</span>
                            @endif
                        </div>
                        <h4 class="mt-3 font-semibold text-slate-900">{{ $event->title }}</h4>
                        <p class="mt-2 text-sm text-slate-500">{{ $event->location }}</p>
                        <p class="mt-3 text-sm text-slate-500">{{ $event->starts_at->format('g:i A') }} - {{ $event->ends_at->format('g:i A') }}</p>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">No published events are scheduled yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
