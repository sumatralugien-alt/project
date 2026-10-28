<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('each role is redirected to its dashboard after login', function (string $role, string $route) {
    $user = User::factory()->create(['role' => $role]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route($route, absolute: false));
})->with([
    ['Administrator', 'dashboard.administrator'],
    ['Staff', 'dashboard.staff'],
    ['Student', 'dashboard.student'],
]);

test('users can only access their own role dashboard', function () {
    Http::fake(['api.open-meteo.com/*' => Http::response(['current' => [
        'temperature_2m' => 25,
        'relative_humidity_2m' => 70,
        'apparent_temperature' => 26,
        'precipitation' => 0,
        'weather_code' => 0,
        'wind_speed_10m' => 5,
        'time' => '2026-10-07T14:00',
    ]])]);

    $user = User::factory()->create(['role' => 'Staff']);

    $this->actingAs($user)->get(route('dashboard.staff'))->assertOk();
    $this->actingAs($user)->get(route('dashboard.administrator'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard.student'))->assertForbidden();
});

test('each dashboard renders a logout button', function (string $role, string $route) {
    Http::fake(['api.open-meteo.com/*' => Http::response(['current' => [
        'temperature_2m' => 25,
        'relative_humidity_2m' => 70,
        'apparent_temperature' => 26,
        'precipitation' => 0,
        'weather_code' => 0,
        'wind_speed_10m' => 5,
        'time' => '2026-10-07T14:00',
    ]])]);

    $user = User::factory()->create(['role' => $role]);

    $response = $this->actingAs($user)->get(route($route));

    $response->assertOk();
    $response->assertSee('action="'.route('logout').'"', false);
    $response->assertSee('type="submit"', false);
    $response->assertSee('Log Out');
    $response->assertSee('bg-rose-400/10', false);
})->with([
    ['Administrator', 'dashboard.administrator'],
    ['Staff', 'dashboard.staff'],
    ['Student', 'dashboard.student'],
]);

test('only administrators can manage users', function () {
    $staff = User::factory()->create(['role' => 'Staff']);
    $student = User::factory()->create(['role' => 'Student']);
    $administrator = User::factory()->create(['role' => 'Administrator']);

    $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
    $this->actingAs($student)->get(route('users.index'))->assertForbidden();
    $this->actingAs($administrator)->get(route('users.index'))->assertOk();
});

test('administrators can see profile photos and initials in user management', function () {
    Storage::fake('public');
    $administrator = User::factory()->create([
        'name' => 'Alex Admin',
        'role' => 'Administrator',
        'profile_photo_path' => 'profile-photos/admin.png',
    ]);
    $user = User::factory()->create([
        'name' => 'Sam User',
        'profile_photo_path' => null,
    ]);
    Storage::disk('public')->put('profile-photos/admin.png', 'image contents');

    $response = $this->actingAs($administrator)->get(route('users.index'));

    $response->assertOk();
    $response->assertSee('src="'.$administrator->profilePhotoUrl().'"', false);
    $response->assertSee('Alex Admin');
    $response->assertSee('>SU</span>', false);
    $response->assertSee('Sam User');
});

test('administrators can create users with an assigned role', function () {
    $administrator = User::factory()->create(['role' => 'Administrator']);

    $response = $this->actingAs($administrator)->post(route('users.store'), [
        'name' => 'New Staff',
        'email' => 'new-staff@example.com',
        'role' => 'Staff',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('users.index', absolute: false));
    $this->assertDatabaseHas('users', ['email' => 'new-staff@example.com', 'role' => 'Staff']);
});

test('administrators receive a validation error when creating a user with an existing email', function () {
    $administrator = User::factory()->create(['role' => 'Administrator']);
    User::factory()->create(['email' => 'staff@gmail.com']);

    $response = $this->actingAs($administrator)
        ->from(route('users.create'))
        ->post(route('users.store'), [
            'name' => 'staff',
            'email' => 'staff@gmail.com',
            'role' => 'Staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('users.create', absolute: false))
        ->assertSessionHasErrors('email');
    $this->assertDatabaseCount('users', 2);
});

test('student records are the same as user records with the Student role', function () {
    $administrator = User::factory()->create(['role' => 'Administrator']);
    $student = User::factory()->create([
        'name' => 'Alice Student',
        'email' => 'alice-student@example.com',
        'role' => 'Student',
        'student_number' => 'S-1001',
    ]);

    $response = $this->actingAs($administrator)->get(route('students.index'));

    $response->assertOk();
    $response->assertSee('Alice Student');
    $response->assertSee('Student ID');
    $response->assertSee('S-1001');
    $this->assertDatabaseHas('users', ['id' => $student->id, 'role' => 'Student']);
});

test('every student gets a unique qr code', function () {
    Storage::fake('public');

    $firstStudent = User::factory()->create([
        'role' => 'Student',
        'student_number' => 'ST-9001',
    ]);
    $secondStudent = User::factory()->create([
        'role' => 'Student',
        'student_number' => 'ST-9002',
    ]);

    expect($firstStudent->qr_code_path)->not->toBeNull()
        ->and($secondStudent->qr_code_path)->not->toBeNull()
        ->and($firstStudent->qr_code_path)->not->toBe($secondStudent->qr_code_path)
        ->and($firstStudent->qrCodeUrl())->not->toBeNull()
        ->and(Storage::disk('public')->exists($firstStudent->qr_code_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($secondStudent->qr_code_path))->toBeTrue();
});

test('student qr code payload includes the student name', function () {
    $student = User::factory()->create([
        'role' => 'Student',
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'student_number' => 'ST-9101',
        'phone' => '09123456789',
        'program' => 'Information Technology',
        'year_level' => 3,
    ]);

    expect($student->qrCodePayload())
        ->toContain('name:Maria Santos')
        ->toContain('student-number:ST-9101')
        ->toContain('email:maria@example.com')
        ->toContain('phone:09123456789')
        ->toContain('program:Information Technology')
        ->toContain('year-level:3');
});

test('student can generate and download their own qr code', function () {
    $student = User::factory()->create([
        'role' => 'Student',
        'student_number' => 'ST-2001',
    ]);

    $generateResponse = $this->actingAs($student)->post(route('profile.qr.generate'));
    $downloadResponse = $this->actingAs($student)->get(route('profile.qr.download'));

    $generateResponse->assertRedirect(route('profile.edit', absolute: false));

    $downloadResponse->assertOk();
    $downloadResponse->assertHeader('Content-Type', 'image/svg+xml');
    expect($student->fresh()->qr_code_path)->not->toBeNull();
});

test('staff can download a csv report of system data', function () {
    $staff = User::factory()->create(['role' => 'Staff']);
    User::factory()->create(['role' => 'Student', 'name' => 'Student One', 'student_number' => 'ST-1001']);

    $response = $this->actingAs($staff)->get(route('reports.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition');
    $response->assertSee('total_users');
    $response->assertSee('Student One');
});

test('staff can download a pdf report of system data', function () {
    $staff = User::factory()->create(['role' => 'Staff']);

    $response = $this->actingAs($staff)->get(route('reports.export.pdf'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
    expect($response->getContent())->toStartWith('%PDF');
});

test('dashboard does not render search and filter controls', function () {
    Http::fake(['api.open-meteo.com/*' => Http::response(['current' => [
        'temperature_2m' => 25,
        'relative_humidity_2m' => 70,
        'apparent_temperature' => 26,
        'precipitation' => 0,
        'weather_code' => 0,
        'wind_speed_10m' => 5,
        'time' => '2026-10-07T14:00',
    ]])]);

    $administrator = User::factory()->create(['role' => 'Administrator']);
    $staff = User::factory()->create(['role' => 'Staff']);

    $administratorResponse = $this->actingAs($administrator)->get(route('dashboard.administrator'));
    $staffResponse = $this->actingAs($staff)->get(route('dashboard.staff'));

    $administratorResponse->assertOk();
    $administratorResponse->assertDontSee('Search & filters');
    $administratorResponse->assertDontSee('Apply filters');

    $staffResponse->assertOk();
    $staffResponse->assertDontSee('Search & filters');
    $staffResponse->assertDontSee('Apply filters');
});
