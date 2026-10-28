<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserManagementController;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/administrator', function () {
        $search = request('search');
        $eventStatus = request('event_status');
        $attendance = request('attendance');
        $monthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $filteredEvents = Event::query()
            ->withCount('registrations')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($eventStatus, fn ($query) => $query->where('status', $eventStatus))
            ->when($attendance === 'open', fn ($query) => $query->where(function ($query) {
                $query->whereNull('capacity')->orWhereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.status = ?) < events.capacity', ['Registered']);
            }))
            ->when($attendance === 'full', fn ($query) => $query->whereNotNull('capacity')->whereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.status = ?) >= events.capacity', ['Registered']))
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $chartMonths = collect(range(5, 0))->map(fn ($monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());
        $registrationChart = $chartMonths->map(fn ($month) => EventRegistration::query()->where('status', 'Registered')->whereBetween('registered_at', [$month, $month->copy()->endOfMonth()])->count())->values();
        $eventChart = $chartMonths->map(fn ($month) => Event::query()->whereBetween('starts_at', [$month, $month->copy()->endOfMonth()])->count())->values();
        $currentRegistrations = EventRegistration::query()->where('status', 'Registered')->where('registered_at', '>=', $monthStart)->count();
        $previousRegistrations = EventRegistration::query()->where('status', 'Registered')->whereBetween('registered_at', [$previousMonthStart, $previousMonthEnd])->count();
        $registrationTrend = $previousRegistrations === 0 ? ($currentRegistrations > 0 ? 100 : 0) : round((($currentRegistrations - $previousRegistrations) / $previousRegistrations) * 100);
        $recentRegistrations = EventRegistration::query()->with(['user', 'event'])->latest('registered_at')->limit(5)->get();
        $recentUsers = User::query()->latest()->limit(4)->get();
        $recentStudents = User::query()->where('role', 'Student')->latest('updated_at')->limit(4)->get();
        $recentEvents = $filteredEvents;

        $recentActivity = collect([])
            ->concat($recentRegistrations->map(fn ($registration) => [
                'title' => ($registration->user?->name ?? 'A student').' registered for '.$registration->event?->title,
                'meta' => $registration->event?->location ?? 'Event registration',
                'time' => $registration->registered_at?->diffForHumans() ?? $registration->created_at->diffForHumans(),
                'timestamp' => $registration->registered_at?->timestamp ?? $registration->created_at->timestamp,
                'tone' => 'emerald',
            ]))
            ->concat($recentUsers->map(fn ($user) => [
                'title' => $user->name.' joined as '.$user->role,
                'meta' => $user->email,
                'time' => $user->created_at->diffForHumans(),
                'timestamp' => $user->created_at->timestamp,
                'tone' => 'indigo',
            ]))
            ->concat($recentStudents->map(fn ($student) => [
                'title' => $student->name.' updated their student profile',
                'meta' => collect([
                    $student->student_number,
                    $student->phone,
                    $student->program,
                    $student->year_level ? 'Year '.$student->year_level : null,
                ])->filter()->implode(' • ') ?: 'Student profile',
                'time' => $student->updated_at->diffForHumans(),
                'timestamp' => $student->updated_at->timestamp,
                'tone' => 'violet',
            ]))
            ->concat($recentEvents->map(fn ($event) => [
                'title' => $event->title.' is scheduled for '.$event->starts_at->format('M j'),
                'meta' => $event->registrations_count.' registrations so far',
                'time' => $event->created_at->diffForHumans(),
                'timestamp' => $event->created_at->timestamp,
                'tone' => 'amber',
            ]))
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();

        return view('dashboards.administrator', [
            'eventCount' => Event::count(),
            'studentCount' => User::query()->where('role', 'Student')->count(),
            'userCount' => User::count(),
            'registrationCount' => EventRegistration::query()->where('status', 'Registered')->count(),
            'upcomingEvents' => $filteredEvents,
            'recentActivity' => $recentActivity,
            'chartLabels' => $chartMonths->map(fn ($month) => $month->format('M'))->values(),
            'registrationChart' => $registrationChart,
            'eventChart' => $eventChart,
            'registrationTrend' => $registrationTrend,
            'activeFilters' => compact('search', 'eventStatus', 'attendance'),
            'weather' => app(WeatherService::class)->current(),
        ]);
    })
        ->middleware('role:Administrator')
        ->name('dashboard.administrator');
    Route::get('/dashboard/staff', function () {
        $search = request('search');
        $eventStatus = request('event_status');
        $attendance = request('attendance');
        $monthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        $filteredEvents = Event::query()
            ->withCount('registrations')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($eventStatus, fn ($query) => $query->where('status', $eventStatus))
            ->when($attendance === 'open', fn ($query) => $query->where(function ($query) {
                $query->whereNull('capacity')->orWhereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.status = ?) < events.capacity', ['Registered']);
            }))
            ->when($attendance === 'full', fn ($query) => $query->whereNotNull('capacity')->whereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_registrations.event_id = events.id AND event_registrations.status = ?) >= events.capacity', ['Registered']))
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();
        $chartMonths = collect(range(5, 0))->map(fn ($monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());
        $registrationChart = $chartMonths->map(fn ($month) => EventRegistration::query()->where('status', 'Registered')->whereBetween('registered_at', [$month, $month->copy()->endOfMonth()])->count())->values();
        $eventChart = $chartMonths->map(fn ($month) => Event::query()->whereBetween('starts_at', [$month, $month->copy()->endOfMonth()])->count())->values();
        $currentRegistrations = EventRegistration::query()->where('status', 'Registered')->where('registered_at', '>=', $monthStart)->count();
        $previousRegistrations = EventRegistration::query()->where('status', 'Registered')->whereBetween('registered_at', [$previousMonthStart, $previousMonthEnd])->count();
        $registrationTrend = $previousRegistrations === 0 ? ($currentRegistrations > 0 ? 100 : 0) : round((($currentRegistrations - $previousRegistrations) / $previousRegistrations) * 100);
        $recentRegistrations = EventRegistration::query()->with(['user', 'event'])->latest('registered_at')->limit(5)->get();
        $recentEvents = $filteredEvents;
        $recentStudents = User::query()->where('role', 'Student')->latest('updated_at')->limit(4)->get();

        $recentActivity = collect([])
            ->concat($recentRegistrations->map(fn ($registration) => [
                'title' => ($registration->user?->name ?? 'A student').' registered for '.$registration->event?->title,
                'meta' => $registration->event?->location ?? 'Event registration',
                'time' => $registration->registered_at?->diffForHumans() ?? $registration->created_at->diffForHumans(),
                'timestamp' => $registration->registered_at?->timestamp ?? $registration->created_at->timestamp,
                'tone' => 'emerald',
            ]))
            ->concat($recentEvents->map(fn ($event) => [
                'title' => $event->title.' is now open for sign-ups',
                'meta' => $event->starts_at->format('M j, Y').' • '.$event->location,
                'time' => $event->created_at->diffForHumans(),
                'timestamp' => $event->created_at->timestamp,
                'tone' => 'teal',
            ]))
            ->concat($recentStudents->map(fn ($student) => [
                'title' => $student->name.' updated their student profile',
                'meta' => collect([
                    $student->student_number,
                    $student->phone,
                    $student->program,
                    $student->year_level ? 'Year '.$student->year_level : null,
                ])->filter()->implode(' • ') ?: 'Student profile',
                'time' => $student->updated_at->diffForHumans(),
                'timestamp' => $student->updated_at->timestamp,
                'tone' => 'violet',
            ]))
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();

        return view('dashboards.staff', [
            'eventCount' => Event::count(),
            'studentCount' => User::query()->where('role', 'Student')->count(),
            'registrationCount' => EventRegistration::query()->where('status', 'Registered')->count(),
            'upcomingEvents' => $filteredEvents,
            'recentActivity' => $recentActivity,
            'chartLabels' => $chartMonths->map(fn ($month) => $month->format('M'))->values(),
            'registrationChart' => $registrationChart,
            'eventChart' => $eventChart,
            'registrationTrend' => $registrationTrend,
            'activeFilters' => compact('search', 'eventStatus', 'attendance'),
            'weather' => app(WeatherService::class)->current(),
        ]);
    })
        ->middleware('role:Staff')
        ->name('dashboard.staff');
    Route::get('/dashboard/student', function () {
        return view('dashboards.student', [
            'upcomingEvents' => Event::query()->where('status', 'Published')->where('starts_at', '>', now())->orderBy('starts_at')->limit(6)->get(),
            'registrationCount' => auth()->user()->registrations()->where('status', 'Registered')->count(),
            'weather' => app(WeatherService::class)->current(),
        ]);
    })
        ->middleware('role:Student')
        ->name('dashboard.student');

    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->dashboardRouteName());
    })->name('dashboard');

    Route::resource('users', UserManagementController::class)
        ->except('show')
        ->middleware('role:Administrator');

    Route::get('/events', [EventController::class, 'index'])->middleware('role:Administrator,Staff,Student')->name('events.index');
    Route::resource('events', EventController::class)->except(['index', 'show'])->middleware('role:Administrator,Staff');
    Route::get('/events/{event}', [EventController::class, 'show'])->middleware('role:Administrator,Staff,Student')->name('events.show');
    Route::resource('students', StudentController::class)->middleware('role:Administrator,Staff');
    Route::get('/reports', [ReportController::class, 'index'])->middleware('role:Administrator,Staff')->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->middleware('role:Administrator,Staff')->name('reports.export');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->middleware('role:Administrator,Staff')->name('reports.export.pdf');
    Route::get('/my-registrations', [EventRegistrationController::class, 'index'])->middleware('role:Student')->name('registrations.index');
    Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])->middleware('role:Student')->name('registrations.store');
    Route::patch('/registrations/{registration}/cancel', [EventRegistrationController::class, 'destroy'])->middleware('role:Student')->name('registrations.destroy');
    Route::delete('/registrations/{registration}', [EventRegistrationController::class, 'delete'])->middleware('role:Student')->name('registrations.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/qr/generate', function () {
        $user = auth()->user();

        abort_unless($user && $user->role === 'Student', 403);

        $user->ensureQrCode();

        return redirect()->route('profile.edit');
    })->name('profile.qr.generate');
    Route::get('/profile/qr/download', function () {
        $user = auth()->user();

        abort_unless($user && $user->role === 'Student', 403);

        if (! $user->qr_code_path) {
            $user->ensureQrCode();
        }

        return response()->file(Storage::disk('public')->path($user->qr_code_path), [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="student-qr-code.svg"',
        ]);
    })->name('profile.qr.download');
});

require __DIR__.'/auth.php';
