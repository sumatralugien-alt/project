<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('students.index', ['students' => User::query()->where('role', 'Student')->latest()->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'Student',
            'student_number' => $validated['student_number'],
            'phone' => $validated['phone'] ?? null,
            'program' => $validated['program'] ?? null,
            'year_level' => $validated['year_level'] ?? null,
            'status' => $validated['status'],
            'password' => bcrypt('password'),
        ]);

        return to_route('students.index')->with('status', 'Student added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student): View
    {
        abort_unless($student->role === 'Student', 404);

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student): View
    {
        abort_unless($student->role === 'Student', 404);

        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->role === 'Student', 404);

        $input = $request->all();

        if ($request->isMethod('PATCH')) {
            $input = array_merge($student->only([
                'student_number',
                'name',
                'email',
                'phone',
                'program',
                'year_level',
                'status',
            ]), $input);
        }

        $student->update(validator($input, $this->rules($student))->validate());
        $student->ensureQrCode();

        return to_route('students.index')->with('status', 'Student updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->role === 'Student', 404);

        $student->delete();

        return to_route('students.index')->with('status', 'Student removed.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?User $student = null): array
    {
        return [
            'student_number' => ['required', 'string', 'max:50', Rule::unique('users', 'student_number')->ignore($student)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student)],
            'phone' => ['nullable', 'string', 'max:40'],
            'program' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'integer', 'min:1', 'max:8'],
            'status' => ['required', 'in:Active,Inactive,Graduated'],
        ];
    }
}
