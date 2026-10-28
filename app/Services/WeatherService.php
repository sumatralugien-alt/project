<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * @return array{
     *     temperature: float,
     *     apparent_temperature: float,
     *     relative_humidity: int,
     *     precipitation: float,
     *     wind_speed: float,
     *     condition: string,
     *     observed_at: string
     * }|null
     */
    public function current(): ?array
    {
        try {
            return Cache::remember('weather.doroluman.current', now()->addMinutes(15), function (): ?array {
                $response = Http::acceptJson()
                    ->timeout(5)
                    ->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => 7.35388,
                        'longitude' => 125.0894147,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                        'timezone' => 'Asia/Manila',
                    ])
                    ->throw();

                $current = $response->json('current');

                if (! is_array($current)
                    || ! is_numeric($current['temperature_2m'] ?? null)
                    || ! is_numeric($current['relative_humidity_2m'] ?? null)
                    || ! is_numeric($current['apparent_temperature'] ?? null)
                    || ! is_numeric($current['precipitation'] ?? null)
                    || ! is_numeric($current['weather_code'] ?? null)
                    || ! is_numeric($current['wind_speed_10m'] ?? null)
                    || ! is_string($current['time'] ?? null)) {
                    Log::warning('Weather API returned an unexpected response for Doroluman.');

                    return null;
                }

                return [
                    'temperature' => (float) $current['temperature_2m'],
                    'apparent_temperature' => (float) $current['apparent_temperature'],
                    'relative_humidity' => (int) $current['relative_humidity_2m'],
                    'precipitation' => (float) $current['precipitation'],
                    'wind_speed' => (float) $current['wind_speed_10m'],
                    'condition' => $this->condition((int) $current['weather_code']),
                    'observed_at' => $current['time'],
                ];
            });
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Weather API request failed for Doroluman.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function condition(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45, 48 => 'Fog',
            51, 53, 55 => 'Drizzle',
            56, 57 => 'Freezing drizzle',
            61, 63, 65 => 'Rain',
            66, 67 => 'Freezing rain',
            71, 73, 75, 77 => 'Snow',
            80, 81, 82 => 'Rain showers',
            85, 86 => 'Snow showers',
            95, 96, 99 => 'Thunderstorm',
            default => 'Conditions unavailable',
        };
    }
}
