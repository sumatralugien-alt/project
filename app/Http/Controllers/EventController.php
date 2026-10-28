<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Event::query()->withCount('registrations')->orderBy('starts_at');
        if ($request->user()->role === 'Student') {
            $query->where('status', 'Published');
        }
        $events = $query->paginate(10);

        if ($request->expectsJson()) {
            return response()->json($events);
        }

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['created_by'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['title']).'-'.Str::lower(Str::random(5));
        Event::create($validated);

        return to_route('events.index')->with('status', 'Event created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): View
    {
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event): View
    {
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($request->validate($this->rules($event)));

        return to_route('events.index')->with('status', 'Event updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return to_route('events.index')->with('status', 'Event deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Event $event = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:Draft,Published,Closed'],
        ];
    }
}
