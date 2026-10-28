<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.google.client_id', 'test-client-id');
    config()->set('services.google.client_secret', 'test-client-secret');
    config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
});

test('login and registration pages offer Google sign-in', function () {
    $this->get('/login')->assertOk()->assertSee('Login with Google');
    $this->get('/register')->assertOk()->assertSee('Sign up with Google');
});

test('Google redirect sends the OAuth request with a session-bound state', function () {
    $response = $this->get(route('google.redirect'));

    $response->assertRedirect();
    parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $parameters);

    expect($parameters['client_id'])->toBe('test-client-id')
        ->and($parameters['redirect_uri'])->toBe('http://localhost/auth/google/callback')
        ->and($parameters['response_type'])->toBe('code')
        ->and($parameters['scope'])->toBe('openid email profile')
        ->and($parameters['code_challenge_method'])->toBe('S256')
        ->and($parameters['code_challenge'])->toBe(
            rtrim(strtr(base64_encode(hash('sha256', session('google_oauth_code_verifier'), true)), '+/', '-_'), '=')
        )
        ->and($parameters['state'])->toBe(session('google_oauth_state'));
});

test('verified Google account creates a student and authenticates them', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
        'openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-user-123',
            'name' => 'Google Student',
            'email' => 'student@example.com',
            'email_verified' => true,
        ]),
    ]);

    $response = $this->withSession([
        'google_oauth_state' => 'valid-state',
        'google_oauth_code_verifier' => 'test-code-verifier',
    ])
        ->get(route('google.callback', ['state' => 'valid-state', 'code' => 'authorization-code']));

    $user = User::query()->where('email', 'student@example.com')->firstOrFail();

    expect($user->role)->toBe('Student')
        ->and($user->google_id)->toBe('google-user-123')
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard.student', absolute: false));
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token');
});

test('verified Google email links to the existing matching user', function () {
    $user = User::factory()->unverified()->create(['email' => 'existing@example.com']);

    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
        'openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-existing-user',
            'name' => 'Updated Name',
            'email' => 'existing@example.com',
            'email_verified' => true,
        ]),
    ]);

    $response = $this->withSession([
        'google_oauth_state' => 'valid-state',
        'google_oauth_code_verifier' => 'test-code-verifier',
    ])
        ->get(route('google.callback', ['state' => 'valid-state', 'code' => 'authorization-code']));

    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1)
        ->and($user->fresh()->google_id)->toBe('google-existing-user')
        ->and($user->fresh()->email_verified_at)->not->toBeNull();
    $response->assertRedirect(route('dashboard.student', absolute: false));
});

test('callback rejects invalid state without contacting Google', function () {
    Http::fake();

    $response = $this->withSession([
        'google_oauth_state' => 'expected-state',
        'google_oauth_code_verifier' => 'test-code-verifier',
    ])
        ->get(route('google.callback', ['state' => 'incorrect-state', 'code' => 'authorization-code']));

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Google Sign-In could not be verified. Please try again.');
    Http::assertNothingSent();
});

test('callback rejects unverified Google email addresses', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
        'openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'unverified-google-user',
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]),
    ]);

    $response = $this->withSession([
        'google_oauth_state' => 'valid-state',
        'google_oauth_code_verifier' => 'test-code-verifier',
    ])
        ->get(route('google.callback', ['state' => 'valid-state', 'code' => 'authorization-code']));

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
    $response->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Google Sign-In requires a valid, verified Google email address.');
});

test('callback handles Google network failures without authenticating', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => fn () => throw new ConnectionException('Connection failed'),
    ]);

    $response = $this->withSession([
        'google_oauth_state' => 'valid-state',
        'google_oauth_code_verifier' => 'test-code-verifier',
    ])
        ->get(route('google.callback', ['state' => 'valid-state', 'code' => 'authorization-code']));

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Google Sign-In is temporarily unavailable. Please try again.');
});

test('Google cancellation returns the user to login with feedback', function () {
    $response = $this->withSession(['google_oauth_state' => 'valid-state'])
        ->get(route('google.callback', ['error' => 'access_denied']));

    $response->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Google Sign-In was cancelled or could not be completed.');
    expect(session()->has('google_oauth_state'))->toBeFalse();
});
