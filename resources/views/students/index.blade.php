<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-600">Directory</p>
                <h2 class="text-2xl font-semibold text-slate-900">Students</h2>
            </div>
            <a href="{{ route('students.create') }}" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Add student</a>
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
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Student ID</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Program</th>
                            <th class="px-6 py-4">Year level</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('students.show', $student) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $student->name }}</a>
                                    <p class="text-slate-500">{{ $student->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $student->student_number ?: 'Not provided' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $student->phone ?: 'Not provided' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $student->program ?: 'Not specified' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $student->year_level ?: 'Not specified' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $student->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="font-medium text-teal-700" href="{{ route('students.edit', $student) }}">Edit</a>
                                    <form class="ml-3 inline" method="POST" action="{{ route('students.destroy', $student) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-rose-600" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-10 text-center text-slate-500" colspan="7">No student records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-4">{{ $students->links() }}</div>
        </div>
    </div>
</x-app-layout>
