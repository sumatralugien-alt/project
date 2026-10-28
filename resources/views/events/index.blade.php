<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">{{ auth()->user()->role === 'Student' ? 'Browse' : 'Operations' }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">Events</h2>
            </div>
            @if (auth()->user()->role !== 'Student')
                <a href="{{ route('events.create') }}" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">Create event</a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Event</th>
                            <th class="px-6 py-4">Schedule</th>
                            <th class="px-6 py-4">Attendance</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($events as $event)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <a class="font-semibold text-slate-900 hover:text-teal-700" href="{{ route('events.show', $event) }}">{{ $event->title }}</a>
                                    <p class="mt-1 text-slate-500">{{ $event->location }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $event->starts_at->format('M j, Y g:i A') }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $event->registrations_count }}{{ $event->capacity ? ' / '.$event->capacity : '' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $event->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if (auth()->user()->role === 'Student')
                                        <a class="inline-flex rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700" href="{{ route('events.show', $event) }}">View</a>
                                    @else
                                        <a class="font-medium text-teal-700" href="{{ route('events.edit', $event) }}">Edit</a>
                                        <form class="ml-3 inline" method="POST" action="{{ route('events.destroy', $event) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-rose-600" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-10 text-center text-slate-500" colspan="5">No events yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $events->links() }}</div>
        </div>
    </div>
</x-app-layout>
