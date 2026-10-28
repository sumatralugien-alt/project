<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Directory</p>
        <h2 class="text-2xl font-semibold text-slate-900">{{ $student->name }}</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="grid gap-8 md:grid-cols-[1fr,220px] md:items-start">
                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student ID</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->student_number ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Program</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->program ?: 'Not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone number</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Year level</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->year_level ?: 'Not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-1 text-slate-900">{{ $student->status }}</dd>
                    </div>
                </dl>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                    @php($student->ensureQrCode())
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Student QR</p>
                    @if ($student->qrCodeUrl())
                        <img src="{{ $student->qrCodeUrl() }}" alt="QR code for {{ $student->name }}" class="mx-auto h-36 w-36 rounded-xl bg-white p-2 shadow-sm">
                    @else
                        <div class="flex h-36 w-36 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-xs font-semibold text-slate-500">QR unavailable</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
