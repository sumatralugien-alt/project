<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('database seeder creates a verified administrator with the configured password', function () {
    config()->set('app.admin_seed_password', 'seeder-test-password');

    $this->seed();

    $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

    expect($admin->name)->toBe('Administrator')
        ->and($admin->role)->toBe('Administrator')
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('seeder-test-password', $admin->password))->toBeTrue();
});

test('database seeder requires the admin password to be configured', function () {
    config()->set('app.admin_seed_password', null);

    expect(fn () => $this->seed())->toThrow(RuntimeException::class, 'Set ADMIN_SEED_PASSWORD');
});
