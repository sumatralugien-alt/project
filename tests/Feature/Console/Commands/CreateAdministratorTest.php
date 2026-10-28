<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin account can be created with a securely prompted password', function () {
    $this->artisan('app:create-admin', ['email' => 'admin@example.com'])
        ->expectsQuestion('Administrator name', 'System Administrator')
        ->expectsQuestion('Password (minimum 12 characters)', 'SecureAdmin!2026')
        ->expectsQuestion('Confirm password', 'SecureAdmin!2026')
        ->expectsOutput('Administrator account created for admin@example.com.')
        ->assertSuccessful();

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect($admin->role)->toBe('Administrator')
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('SecureAdmin!2026', $admin->password))->toBeTrue();
});

test('admin account is not created when the password is weak', function () {
    $this->artisan('app:create-admin', ['email' => 'admin@example.com'])
        ->expectsQuestion('Administrator name', 'System Administrator')
        ->expectsQuestion('Password (minimum 12 characters)', 'admin@gmail.com')
        ->expectsQuestion('Confirm password', 'admin@gmail.com')
        ->assertFailed();

    expect(User::query()->where('email', 'admin@example.com')->exists())->toBeFalse();
});

test('admin account is not created for an email that already exists', function () {
    User::factory()->create(['email' => 'admin@example.com']);

    $this->artisan('app:create-admin', ['email' => 'admin@example.com'])
        ->expectsOutputToContain('The email has already been taken.')
        ->assertFailed();

    expect(User::query()->where('email', 'admin@example.com')->count())->toBe(1);
});
