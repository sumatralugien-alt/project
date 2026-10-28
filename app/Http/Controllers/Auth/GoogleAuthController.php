<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->hasGoogleCredentials()) {
            return $this->loginWithStatus('Google Sign-In is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.');
        }

        $state = Str::random(40);
        $codeVerifier = Str::random(64);
        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_code_verifier', $codeVerifier);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->query('error') !== null) {
            $request->session()->forget(['google_oauth_state', 'google_oauth_code_verifier']);

            return $this->loginWithStatus('Google Sign-In was cancelled or could not be completed.');
        }

        $expectedState = $request->session()->pull('google_oauth_state');
        $codeVerifier = $request->session()->pull('google_oauth_code_verifier');
        $providedState = $request->query('state');

        if (
            ! is_string($expectedState) || ! is_string($providedState)
            || ! hash_equals($expectedState, $providedState)
            || ! is_string($codeVerifier)
        ) {
            return $this->loginWithStatus('Google Sign-In could not be verified. Please try again.');
        }

        if (! $this->hasGoogleCredentials()) {
            return $this->loginWithStatus('Google Sign-In is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.');
        }

        $authorizationCode = $request->query('code');

        if (! is_string($authorizationCode) || $authorizationCode === '') {
            return $this->loginWithStatus('Google Sign-In did not return an authorization code. Please try again.');
        }

        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $authorizationCode,
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => config('services.google.redirect'),
                    'grant_type' => 'authorization_code',
                    'code_verifier' => $codeVerifier,
                ]);

            if (! $tokenResponse->successful()) {
                return $this->loginWithStatus('Google Sign-In could not be completed. Please try again.');
            }

            $accessToken = $tokenResponse->json('access_token');

            if (! is_string($accessToken) || $accessToken === '') {
                return $this->loginWithStatus('Google did not provide an access token. Please try again.');
            }

            $profileResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->get('https://openidconnect.googleapis.com/v1/userinfo');

            if (! $profileResponse->successful()) {
                return $this->loginWithStatus('Google could not verify your account details. Please try again.');
            }
        } catch (ConnectionException) {
            return $this->loginWithStatus('Google Sign-In is temporarily unavailable. Please try again.');
        }

        $profile = $profileResponse->json();
        $googleId = is_array($profile) ? ($profile['sub'] ?? null) : null;
        $name = is_array($profile) ? ($profile['name'] ?? null) : null;
        $email = is_array($profile) ? ($profile['email'] ?? null) : null;
        $emailVerified = is_array($profile) ? ($profile['email_verified'] ?? false) : false;

        if (
            ! is_string($googleId) || $googleId === ''
            || ! is_string($name) || trim($name) === ''
            || ! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || $emailVerified !== true
        ) {
            return $this->loginWithStatus('Google Sign-In requires a valid, verified Google email address.');
        }

        $email = Str::lower($email);

        $user = DB::transaction(function () use ($googleId, $name, $email): ?User {
            $user = User::query()->where('google_id', $googleId)->first()
                ?? User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user && $user->google_id !== null && $user->google_id !== $googleId) {
                return null;
            }

            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'role' => 'Student',
                    'password' => Str::random(64),
                    'google_id' => $googleId,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();

                return $user;
            }

            $user->google_id = $googleId;
            $user->email_verified_at ??= now();
            $user->save();

            return $user;
        });

        if (! $user) {
            return $this->loginWithStatus('This email is already linked to a different Google account. Please contact an administrator.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRouteName());
    }

    private function hasGoogleCredentials(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    private function loginWithStatus(string $message): RedirectResponse
    {
        return redirect()->route('login')->with('status', $message);
    }
}
