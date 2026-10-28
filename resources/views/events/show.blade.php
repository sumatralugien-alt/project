<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Event details</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $event->title }}</h2>
            </div>
            <a class="text-sm font-semibold text-teal-700" href="{{ route('events.index') }}">Back to events</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-teal-700">{{ $event->starts_at->format('l, F j, Y') }}</p>
                    <p class="mt-2 text-slate-600">{{ $event->starts_at->format('g:i A') }} - {{ $event->ends_at->format('g:i A') }} · {{ $event->location }}</p>
                </div>

                @php
                    $registeredCount = $event->registrations()->where('status', 'Registered')->count();
                    $isFull = $event->capacity !== null && $registeredCount >= $event->capacity;
                    $isClosed = $event->status === 'Closed';
                    $isOpen = $event->status === 'Published' && !$isClosed && !$isFull && !$event->ends_at->isPast();
                @endphp

                @if ($isOpen)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Registration open</span>
                @elseif ($isClosed)
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Closed</span>
                @elseif ($isFull)
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Full</span>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $event->status }}</span>
                @endif
            </div>

            <p class="mt-8 whitespace-pre-line leading-7 text-slate-600">{{ $event->description }}</p>

            <div class="mt-8 border-t border-slate-100 pt-5 text-sm text-slate-500">
                {{ $registeredCount }} registered{{ $event->capacity ? ' of '.$event->capacity : '' }}
            </div>

            @if (auth()->user()->role === 'Student' && $isOpen)
                <form class="mt-5" method="POST" action="{{ route('registrations.store', $event) }}">
                    @csrf
                    <x-primary-button>Register for event</x-primary-button>
                </form>
            @elseif (auth()->user()->role === 'Student' && $isClosed)
                <p class="mt-5 text-sm font-medium text-slate-600">Registration is closed for this event.</p>
            @elseif (auth()->user()->role === 'Student' && $isFull)
                <p class="mt-5 text-sm font-medium text-amber-700">This event is full and no longer accepting registrations.</p>
            @endif
        </div>
    </div>
</x-app-layout>
