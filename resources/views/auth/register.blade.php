<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-700">Start here</p>
        <h1 class="mt-3 font-[Space_Grotesk] text-4xl font-semibold tracking-tight text-slate-950">Make your place in the room.</h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">Create an Eventive account and find your next thing to show up for.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Full name</label>
            <input id="name" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white/70 px-4 py-3.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-400 focus:ring-4 focus:ring-teal-100" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your name">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="text-sm font-semibold text-slate-700">Email address</label>
            <input id="email" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white/70 px-4 py-3.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-400 focus:ring-4 focus:ring-teal-100" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                <input id="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white/70 px-4 py-3.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-400 focus:ring-4 focus:ring-teal-100" type="password" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm password</label>
                <input id="password_confirmation" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white/70 px-4 py-3.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-400 focus:ring-4 focus:ring-teal-100" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">Create account</button>
    </form>

    <div class="my-6 flex items-center gap-4 text-xs font-medium uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        <span>or continue with</span>
        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <a href="{{ route('google.redirect') }}" class="flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-teal-100">
        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 48 48">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.25 5.48-4.72 7.18l7.62 5.91c4.45-4.1 7.14-10.14 7.14-17.56Z"/>
            <path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.75-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.93 7.55 2.56 10.78l7.97-6.19Z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.89l-7.62-5.91c-2.12 1.43-4.84 2.3-8.28 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
        </svg>
        Sign up with Google
    </a>

    <p class="mt-8 text-center text-sm text-slate-500">Already have an account? <a href="{{ route('login') }}" class="font-bold text-teal-700 transition hover:text-teal-500">Sign in</a></p>

</x-guest-layout>