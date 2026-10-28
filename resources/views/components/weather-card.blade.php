@props(['weather' => null])

<section class="overflow-hidden rounded-3xl border border-sky-100 bg-gradient-to-br from-white via-sky-50 to-teal-50 p-6 shadow-sm">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">Local weather</p>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">Doroluman, Arakan</h3>
            <p class="text-sm text-slate-500">North Cotabato, Philippines</p>
        </div>

        @if ($weather)
            <div class="text-left sm:text-right">
                <p class="text-4xl font-semibold tracking-tight text-slate-900">{{ round($weather['temperature']) }}°C</p>
                <p class="mt-1 font-medium text-teal-800">{{ $weather['condition'] }}</p>
                <p class="mt-1 text-sm text-slate-500">Feels like {{ round($weather['apparent_temperature']) }}°C</p>
            </div>
        @else
            <p class="max-w-sm text-sm text-slate-600" role="status">Current weather is temporarily unavailable. Please try again later.</p>
        @endif
    </div>

    @if ($weather)
        <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-sky-100 pt-4 sm:grid-cols-4">
            <div>
                <dt class="text-xs text-slate-500">Humidity</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $weather['relative_humidity'] }}%</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Precipitation</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $weather['precipitation'] }} mm</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Wind</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ $weather['wind_speed'] }} km/h</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Updated</dt>
                <dd class="mt-1 font-semibold text-slate-800">{{ \Illuminate\Support\Carbon::parse($weather['observed_at'])->format('g:i A') }}</dd>
            </div>
        </dl>
    @endif

    <p class="mt-4 text-xs text-slate-500">
        Weather data by <a href="https://open-meteo.com/" target="_blank" rel="noreferrer" class="font-medium text-teal-700 underline">Open-Meteo</a>
    </p>
</section>
