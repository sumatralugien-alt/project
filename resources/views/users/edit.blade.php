<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User</h2></x-slot>
    <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8"><div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')
            @include('users.form', ['submitLabel' => 'Update user'])
        </form>
    </div></div></div>
</x-app-layout>