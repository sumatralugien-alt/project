<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::query()->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => User::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);
        } catch (QueryException $exception) {
            if (! User::query()->where('email', $validated['email'])->exists()) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'email' => 'The email has already been taken.',
            ]);
        }

        return to_route('users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user, 'roles' => User::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $input = $request->all();

        if ($request->isMethod('PATCH')) {
            $input = array_merge($user->only(['name', 'email', 'role']), $input);
        }

        $validated = validator($input, [
            ...$this->rules($user),
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $validated = $validated->validate();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            ...filled($validated['password'] ?? null) ? ['password' => Hash::make($validated['password'])] : [],
        ]);

        if ($user->role === 'Student') {
            $user->ensureQrCode();
        }

        return to_route('users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');

        $user->delete();

        return to_route('users.index')->with('status', 'User deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }
}
