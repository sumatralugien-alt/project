<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Insights</p>
                <h2 class="text-2xl font-semibold text-slate-900">Operations report</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.export') }}" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                    Download CSV
                </a>
                <a href="{{ route('reports.export.pdf') }}" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500">
                    Download PDF
                </a>
                <a href="{{ route('dashboard') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300">
                    Back to dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-5 text-white shadow-sm">
                <p class="text-sm text-slate-300">Total users</p>
                <p class="mt-2 text-3xl font-semibold">{{ $report['total_users'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Students</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['total_students'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Staff</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['total_staff'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Events</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['total_events'] }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Student status overview</h3>
                <div class="mt-5 space-y-4">
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm text-slate-600">
                            <span>Active</span>
                            <span>{{ $report['active_students'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ $report['total_students'] ? ($report['active_students'] / $report['total_students']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm text-slate-600">
                            <span>Inactive</span>
                            <span>{{ $report['inactive_students'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-amber-400" style="width: {{ $report['total_students'] ? ($report['inactive_students'] / $report['total_students']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm text-slate-600">
                            <span>Graduated</span>
                            <span>{{ $report['graduated_students'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-violet-500" style="width: {{ $report['total_students'] ? ($report['graduated_students'] / $report['total_students']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Event posting summary</h3>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-sm text-emerald-700">Published</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $report['published_events'] }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-100 p-4">
                        <p class="text-sm text-slate-600">Draft</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['draft_events'] }}</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Recent students</h3>
                <span class="text-sm text-slate-500">Last 10 records</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Student #</th>
                            <th class="px-4 py-3 font-medium">Program</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($students as $student)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $student->student_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $student->program ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                        @if($student->status === 'Active') bg-emerald-100 text-emerald-700
                                        @elseif($student->status === 'Inactive') bg-amber-100 text-amber-700
                                        @else bg-violet-100 text-violet-700
                                        @endif">
                                        {{ $student->status ?? 'Active' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">No student records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Recent events</h3>
                <span class="text-sm text-slate-500">Latest activity</span>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse ($events as $event)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-slate-900">{{ $event->title }}</p>
                            <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700">{{ $event->status }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $event->location }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $event->starts_at->format('M j, Y') }}</p>
                    </div>
                @empty
                    <p class="py-4 text-slate-500">No events available.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
