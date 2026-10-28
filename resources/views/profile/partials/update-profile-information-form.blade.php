<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            @if ($user->profilePhotoUrl())
                <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover ring-4 ring-teal-50">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-500 text-lg font-bold text-slate-950 ring-4 ring-teal-50">{{ $user->profileInitials() }}</div>
            @endif
            <div>
                <label for="profile_photo" class="text-sm font-semibold text-slate-700">Profile picture</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-700 hover:file:bg-teal-100">
                <div class="mt-3 flex items-center gap-2">
                    <input id="remove_profile_photo" name="remove_profile_photo" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <label for="remove_profile_photo" class="text-sm text-slate-600">Remove current picture</label>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if ($user->role === 'Student')
            <div>
                <x-input-label for="student_number" value="Student ID" />
                <x-text-input id="student_number" name="student_number" type="text" class="mt-1 block w-full" :value="old('student_number', $user->student_number)" required autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('student_number')" />
            </div>

            <div>
                <x-input-label for="phone" value="Phone number" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="program" value="Program" />
                <x-text-input id="program" name="program" type="text" class="mt-1 block w-full" :value="old('program', $user->program)" />
                <x-input-error class="mt-2" :messages="$errors->get('program')" />
            </div>

            <div>
                <x-input-label for="year_level" value="Year level" />
                <x-text-input id="year_level" name="year_level" type="number" min="1" max="8" class="mt-1 block w-full" :value="old('year_level', $user->year_level)" />
                <x-input-error class="mt-2" :messages="$errors->get('year_level')" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    @if ($user->role === 'Student')
        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Student QR code</p>
                    <p class="text-xs text-slate-500">Generate or download your unique student QR.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('profile.qr.generate') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-500">Generate QR</button>
                    </form>
                    @if ($user->qrCodeUrl())
                        <a href="{{ route('profile.qr.download') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">Download</a>
                    @endif
                </div>
            </div>

            @if ($user->qrCodeUrl())
                <div class="mt-4 flex justify-center rounded-xl bg-white p-3">
                    <img src="{{ $user->qrCodeUrl() }}" alt="Student QR code" class="h-32 w-32 rounded-lg border border-slate-200 bg-white p-2 shadow-sm">
                </div>
            @endif
        </div>
    @endif
</section>
