<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Event operations</p>
                <h2 class="text-2xl font-semibold text-slate-900">Staff workspace</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300">
                    View reports
                </a>
                <a href="{{ route('events.create') }}" class="inline-flex items-center rounded-full bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-500">
                    Create event
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 rounded-[2rem] bg-gradient-to-br from-white via-sky-50/80 to-teal-50 px-4 py-8 shadow-sm shadow-slate-200/70 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('events.index') }}" class="rounded-2xl bg-gradient-to-br from-teal-500 via-cyan-500 to-sky-500 p-6 text-white shadow-lg shadow-cyan-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-cyan-50">Events</p>
                    <span class="rounded-full bg-white/20 p-2 text-white">◎</span>
                </div>
                <p class="mt-2 text-3xl font-semibold">{{ $eventCount }}</p>
                <p class="mt-5 text-sm font-semibold text-teal-300">Manage schedule</p>
            </a>

            <a href="{{ route('students.index') }}" class="rounded-2xl border border-sky-100 bg-white/90 p-6 shadow-sm shadow-sky-100 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Students</p>
                    <span class="rounded-full bg-emerald-100 p-2 text-emerald-700">◌</span>
                </div>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $studentCount }}</p>
                <p class="mt-5 text-sm font-semibold text-teal-700">Open directory</p>
            </a>

            <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 p-6 shadow-sm shadow-violet-100">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Registrations</p>
                    <span class="rounded-full bg-violet-100 p-2 text-violet-700">◍</span>
                </div>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $registrationCount }}</p>
                <p class="mt-5 text-sm font-semibold text-violet-700">Live signups</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-6 shadow-sm shadow-amber-100">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-amber-700">Workflow</p>
                    <span class="rounded-full bg-white p-2 text-amber-700">✓</span>
                </div>
                <p class="mt-2 font-semibold text-amber-950">Publish events, track capacity, and keep student records current.</p>
            </div>
        </div>

        <x-weather-card :weather="$weather ?? null" />

        <section class="rounded-3xl border border-sky-100 bg-white/90 p-6 shadow-lg shadow-sky-100">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-600">Performance studio</p>
                    <h3 class="mt-1 text-xl font-semibold text-slate-900">Attendance and event momentum</h3>
                </div>
                <p class="text-sm text-slate-400">Last six months</p>
            </div>
            <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-cyan-50 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-700">Registration volume</p>
                        <p class="text-sm font-semibold {{ $registrationTrend >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $registrationTrend >= 0 ? '+' : '' }}{{ $registrationTrend }}% vs last month</p>
                    </div>
                    <div class="mt-6 flex h-40 items-end gap-2 sm:gap-4">
                        @foreach ($registrationChart as $index => $value)
                            <div class="flex min-w-0 flex-1 flex-col items-center gap-2"><div class="flex h-32 w-full items-end"><div class="w-full rounded-t-lg bg-gradient-to-t from-teal-500 to-cyan-300" style="height: {{ $value > 0 ? max(12, min(100, ($value / max(1, $registrationChart->max())) * 100)) : 8 }}%" title="{{ $value }} registrations"></div></div><span class="text-[11px] text-slate-500">{{ $chartLabels[$index] }}</span></div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
                    <div class="flex items-center justify-between"><p class="text-sm font-semibold text-slate-700">Event performance</p><span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Created</span></div>
                    <div class="mt-6 flex h-40 items-end gap-2 sm:gap-4">
                        @foreach ($eventChart as $index => $value)
                            <div class="flex min-w-0 flex-1 flex-col items-center gap-2"><div class="flex h-32 w-full items-end"><div class="w-full rounded-t-lg bg-gradient-to-t from-amber-500 to-yellow-300" style="height: {{ $value > 0 ? max(12, min(100, ($value / max(1, $eventChart->max())) * 100)) : 8 }}%" title="{{ $value }} events"></div></div><span class="text-[11px] text-slate-500">{{ $chartLabels[$index] }}</span></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.6fr_0.9fr]">
            <section class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-lg shadow-slate-200/70">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Activity feed</h3>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Updated</span>
                </div>

                <div class="space-y-4">
                    @forelse ($recentActivity as $item)
                        <div class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-3">
                            <div class="mt-1 h-2.5 w-2.5 rounded-full bg-{{ $item['tone'] }}-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-800">{{ $item['title'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item['meta'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs text-slate-400">{{ $item['time'] }}</span>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-500">No activity yet.</p>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Quick actions</h3>
                        <span class="text-xs uppercase tracking-[0.18em] text-slate-400">Fast access</span>
                    </div>
                    <div class="space-y-3">
                        <a href="{{ route('events.create') }}" class="flex items-center justify-between rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                            <span>Create event</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('students.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                            <span>View students</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('reports.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                            <span>Generate report</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('events.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                            <span>Open event list</span>
                            <span>→</span>
                        </a>
                    </div>
                </section>

            </aside>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Next on the calendar</h3>
                <a class="text-sm font-semibold text-teal-700" href="{{ route('events.create') }}">Create event</a>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse ($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="rounded-xl bg-slate-50 p-4 transition hover:bg-teal-50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $event->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $event->starts_at->format('M j, Y g:i A') }} · {{ $event->location }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-teal-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-700">Live</span>
                        </div>
                    </a>
                @empty
                    <p class="py-4 text-slate-500">No upcoming events.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>