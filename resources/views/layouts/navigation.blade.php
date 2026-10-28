<nav x-data="{ open: false }" class="relative z-20 shrink-0 lg:w-72">
    <div class="flex h-16 items-center justify-between border-b border-white/10 bg-slate-950 px-5 text-white lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-lg font-bold tracking-tight"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-cyan-400 text-sm text-slate-950">EV</span>Eventive</a>
        <button @click="open = ! open" type="button" class="rounded-xl p-2 text-slate-300 transition hover:bg-white/10 hover:text-white" aria-label="Toggle navigation">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/><path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <aside class="hidden min-h-screen flex-col bg-slate-950 px-4 py-6 text-white lg:flex lg:sticky lg:top-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 text-lg font-bold tracking-tight"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-cyan-400 text-sm text-slate-950 shadow-lg shadow-teal-950/40">EV</span><span>Eventive</span></a>
        <div class="mt-12 px-3"><p class="text-[10px] font-bold uppercase tracking-[0.24em] text-teal-300">{{ Auth::user()->role }} space</p><p class="mt-2 text-sm text-slate-400">Your event command center</p></div>

        <div class="mt-6 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard*') ? 'bg-teal-400 text-slate-950 shadow-lg shadow-teal-950/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg {{ request()->routeIs('dashboard*') ? 'bg-slate-950/10' : 'bg-white/10' }}">◈</span>Dashboard</a>
            @if (in_array(Auth::user()->role, ['Administrator', 'Staff'], true))
                <a href="{{ route('events.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('events.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">◫</span>Events</a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('students.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">◎</span>Students</a>
            @else
                <a href="{{ route('events.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('events.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">◫</span>Browse events</a>
                <a href="{{ route('registrations.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('registrations.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">✓</span>My registrations</a>
            @endif
            @if (Auth::user()->role === 'Administrator')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('users.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">＋</span>Users</a>
            @endif
            @if (in_array(Auth::user()->role, ['Administrator', 'Staff'], true))
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">▤</span>Reports</a>
            @endif
        </div>

        <div class="mt-auto border-t border-white/10 pt-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-white/10">
                @if (Auth::user()->profilePhotoUrl())
                    <img src="{{ Auth::user()->profilePhotoUrl() }}" alt="{{ Auth::user()->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-teal-300/40">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-400 font-bold text-sm text-slate-950">{{ Auth::user()->profileInitials() }}</span>
                @endif
                <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-white">{{ Auth::user()->name }}</span><span class="block truncate text-xs text-slate-400">{{ Auth::user()->email }}</span></span><span class="text-slate-500">›</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-rose-300 transition hover:bg-rose-400/10 hover:text-rose-200"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-400/10">↪</span>Log Out</button></form>
        </div>
    </aside>

    <div x-show="open" x-cloak class="border-b border-slate-800 bg-slate-950 px-5 pb-5 text-white lg:hidden">
        <div class="space-y-1 pt-3">
            <a href="{{ route('dashboard') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Dashboard</a>
            @if (in_array(Auth::user()->role, ['Administrator', 'Staff'], true))
                <a href="{{ route('events.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Events</a>
                <a href="{{ route('students.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Students</a>
            @else
                <a href="{{ route('events.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Browse events</a>
                <a href="{{ route('registrations.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">My registrations</a>
            @endif
            @if (Auth::user()->role === 'Administrator')<a href="{{ route('users.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Users</a>@endif
            @if (in_array(Auth::user()->role, ['Administrator', 'Staff'], true))<a href="{{ route('reports.index') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Reports</a>@endif
            <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="block w-full rounded-xl px-3 py-3 text-start text-sm font-semibold text-rose-300 hover:bg-rose-400/10">Log Out</button></form>
        </div>
    </div>
</nav>