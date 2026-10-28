<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('student profile details can be updated and are visible to staff and administrators', function () {
    Storage::fake('public');

    $student = User::factory()->create([
        'role' => 'Student',
        'student_number' => 'ST-1001',
        'phone' => null,
        'program' => null,
        'year_level' => null,
    ]);
    $qrCodePath = $student->qr_code_path;
    $originalQrCode = Storage::disk('public')->get($qrCodePath);

    $response = $this
        ->actingAs($student)
        ->patch('/profile', [
            'name' => $student->name,
            'email' => $student->email,
            'student_number' => 'ST-2002',
            'phone' => '555-0100',
            'program' => 'Computer Science',
            'year_level' => 2,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/profile');
    $student->refresh();

    expect($student->student_number)->toBe('ST-2002')
        ->and($student->phone)->toBe('555-0100')
        ->and($student->program)->toBe('Computer Science')
        ->and($student->year_level)->toBe(2)
        ->and(Storage::disk('public')->get($qrCodePath))->not->toBe($originalQrCode)
        ->and($student->qrCodePayload())->toContain('phone:555-0100')
        ->toContain('program:Computer Science')
        ->toContain('year-level:2');

    Http::fake(['api.open-meteo.com/*' => Http::response(['current' => [
        'temperature_2m' => 25,
        'relative_humidity_2m' => 70,
        'apparent_temperature' => 26,
        'precipitation' => 0,
        'weather_code' => 0,
        'wind_speed_10m' => 5,
        'time' => '2026-10-07T14:00',
    ]])]);

    foreach ([
        ['Administrator', 'dashboard.administrator'],
        ['Staff', 'dashboard.staff'],
    ] as [$role, $dashboardRoute]) {
        $viewer = User::factory()->create(['role' => $role]);

        $this->actingAs($viewer)
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('ST-2002')
            ->assertSee('555-0100')
            ->assertSee('Computer Science')
            ->assertSee('Year level')
            ->assertSee('2');

        $this->actingAs($viewer)
            ->get(route($dashboardRoute))
            ->assertOk()
            ->assertSee('ST-2002')
            ->assertSee('555-0100')
            ->assertSee('Computer Science')
            ->assertSee('Year 2');
    }
});

test('student number must be unique and year level must be in range', function () {
    $existingStudent = User::factory()->create([
        'role' => 'Student',
        'student_number' => 'ST-3003',
    ]);
    $student = User::factory()->create(['role' => 'Student']);

    $response = $this
        ->actingAs($student)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $student->name,
            'email' => $student->email,
            'student_number' => $existingStudent->student_number,
            'year_level' => 9,
        ]);

    $response->assertSessionHasErrors(['student_number', 'year_level'])->assertRedirect('/profile');
    expect($student->fresh()->student_number)->toBe($student->student_number);
});

test('user can upload a gif profile picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('profile.gif', 100, 'image/gif'),
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('user can upload a profile picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('profile.png', 100, 'image/png'),
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('user can delete their profile picture', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Storage::disk('public')->put('profile-photos/old-photo.png', 'contents');
    $user->update(['profile_photo_path' => 'profile-photos/old-photo.png']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_profile_photo' => true,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('profile-photos/old-photo.png');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
