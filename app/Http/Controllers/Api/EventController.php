<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Event::query()->orderBy('starts_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['created_by'] = $request->user()?->id ?? User::query()->value('id') ?? User::factory()->create()->id;
        $validated['slug'] = Str::slug($validated['title']).'-'.Str::lower(Str::random(5));

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $input = $request->all();

        if ($request->isMethod('PATCH')) {
            $input = array_merge($event->only([
                'title',
                'description',
                'location',
                'starts_at',
                'ends_at',
                'capacity',
                'status',
            ]), $input);
        }

        $event->update(validator($input, $this->rules($event))->validate());

        return response()->json($event->fresh());
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->noContent();
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
