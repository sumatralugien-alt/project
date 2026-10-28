<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        return view('registrations.index', ['registrations' => $request->user()->registrations()->with('event')->latest()->get()]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($event, $request): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

            abort_unless($lockedEvent->status === 'Published', 422, 'This event is not open for registration.');
            abort_if($lockedEvent->ends_at->isPast(), 422, 'This event has ended.');
            abort_if($lockedEvent->capacity !== null && $lockedEvent->registrations()->where('status', 'Registered')->count() >= $lockedEvent->capacity, 422, 'This event is full.');

            EventRegistration::updateOrCreate(
                ['event_id' => $lockedEvent->id, 'user_id' => $request->user()->id],
                ['status' => 'Registered', 'registered_at' => now()],
            );
        });

        return to_route('events.show', $event)->with('status', 'You are registered for this event.');
    }

    public function destroy(Request $request, EventRegistration $registration): RedirectResponse
    {
        abort_unless($registration->user_id === $request->user()->id, 403);
        $registration->update(['status' => 'Cancelled']);

        return to_route('registrations.index')->with('status', 'Registration cancelled.');
    }

    public function delete(Request $request, EventRegistration $registration): RedirectResponse
    {
        abort_unless($registration->user_id === $request->user()->id, 403);
        abort_unless($registration->status === 'Cancelled', 422, 'Only cancelled registrations can be deleted.');

        $registration->delete();

        return to_route('registrations.index')->with('status', 'Registration deleted.');
    }
}
