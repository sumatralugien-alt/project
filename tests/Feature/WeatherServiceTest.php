<?php

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('weather service returns cached current conditions for Doroluman', function () {
    Http::fake([
        'api.open-meteo.com/*' => Http::response([
            'current' => [
                'temperature_2m' => 29.4,
                'relative_humidity_2m' => 76,
                'apparent_temperature' => 34.1,
                'precipitation' => 0.2,
                'weather_code' => 2,
                'wind_speed_10m' => 8.5,
                'time' => '2026-10-07T14:00',
            ],
        ]),
    ]);

    $service = app(WeatherService::class);
    $weather = $service->current();
    $cachedWeather = $service->current();

    expect($weather)->toBe([
        'temperature' => 29.4,
        'apparent_temperature' => 34.1,
        'relative_humidity' => 76,
        'precipitation' => 0.2,
        'wind_speed' => 8.5,
        'condition' => 'Partly cloudy',
        'observed_at' => '2026-10-07T14:00',
    ])->and($cachedWeather)->toBe($weather);

    Http::assertSentCount(1);
    Http::assertSent(function ($request): bool {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return str_contains($request->url(), 'api.open-meteo.com/v1/forecast')
            && (float) ($query['latitude'] ?? 0) === 7.35388
            && (float) ($query['longitude'] ?? 0) === 125.0894147
            && ($query['timezone'] ?? null) === 'Asia/Manila';
    });
});

test('all dashboards show the local weather conditions', function () {
    Cache::flush();
    Http::fake([
        'api.open-meteo.com/*' => Http::response([
            'current' => [
                'temperature_2m' => 28,
                'relative_humidity_2m' => 80,
                'apparent_temperature' => 32,
                'precipitation' => 0,
                'weather_code' => 1,
                'wind_speed_10m' => 6,
                'time' => '2026-10-07T14:00',
            ],
        ]),
    ]);

    expect(app(WeatherService::class)->current())->not->toBeNull();

    foreach ([
        ['Administrator', 'dashboard.administrator'],
        ['Staff', 'dashboard.staff'],
        ['Student', 'dashboard.student'],
    ] as [$role, $route]) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get(route($route));

        $response->assertOk()
            ->assertViewHas('weather', fn ($weather): bool => is_array($weather))
            ->assertSee('Doroluman, Arakan')
            ->assertSee('28°C')
            ->assertSee('Mainly clear')
            ->assertSee('Humidity');
    }

    Http::assertSentCount(1);
});

test('dashboard remains available when the weather api is unavailable', function () {
    Http::fake([
        'api.open-meteo.com/*' => Http::response([], 503),
    ]);

    $student = User::factory()->create(['role' => 'Student']);

    $this->actingAs($student)
        ->get(route('dashboard.student'))
        ->assertOk()
        ->assertSee('Doroluman, Arakan')
        ->assertSee('Current weather is temporarily unavailable.');
});
