<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = config('app.admin_seed_password');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new RuntimeException('Set ADMIN_SEED_PASSWORD before running the database seeder.');
        }

        User::query()->firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'role' => 'Administrator',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@gmail.com',
        ]);

        $this->call(EventSeeder::class);
    }
}
