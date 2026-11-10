<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('API registration creates a student and returns a bearer token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'API Student',
        'email' => 'api-student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.name', 'API Student')
        ->assertJsonPath('user.email', 'api-student@example.com')
        ->assertJsonPath('user.role', 'Student')
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

    expect(User::query()->where('email', 'api-student@example.com')->exists())->toBeTrue();
    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

test('API registration returns validation errors for invalid input', function () {
    $this->postJson('/api/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('API login returns a token for valid credentials and unauthorized for invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'api-login@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonStructure(['token']);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

test('internal API routes require a valid bearer token', function () {
    $this->get('/api/event-records')
        ->assertUnauthorized()
        ->assertJsonStructure(['message']);
    $this->getJson('/api/event-records')->assertUnauthorized();
    $this->withToken('invalid-token')->getJson('/api/event-records')->assertUnauthorized();

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)->getJson('/api/event-records')->assertOk();
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    Auth::forgetGuards();

    $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
});

test('authenticated user endpoint returns the current user without sensitive fields', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonMissingPath('user.password');
});
