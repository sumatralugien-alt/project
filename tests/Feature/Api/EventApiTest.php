<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('event api supports listing, creating, updating, and deleting events', function () {
    $event = Event::factory()->create();

    $listResponse = $this->getJson('/api/events');
    $listResponse->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'id',
                'title',
                'description',
                'location',
                'starts_at',
                'ends_at',
                'capacity',
                'status',
            ]],
        ]);

    $createResponse = $this->postJson('/api/events', [
        'title' => 'Community Workshop',
        'description' => 'A practical workshop for students.',
        'location' => 'Main Hall',
        'starts_at' => now()->addDay()->toDateTimeString(),
        'ends_at' => now()->addDay()->addHours(2)->toDateTimeString(),
        'capacity' => 30,
        'status' => 'Published',
    ]);

    $createResponse->assertCreated();
    $eventId = $createResponse->json('id');

    $showResponse = $this->getJson('/api/events/'.$eventId);
    $showResponse->assertOk()->assertJsonPath('id', $eventId);

    $updateResponse = $this->putJson('/api/events/'.$eventId, [
        'title' => 'Updated Community Workshop',
        'description' => 'Updated workshop description.',
        'location' => 'Science Building',
        'starts_at' => now()->addDays(2)->toDateTimeString(),
        'ends_at' => now()->addDays(2)->addHours(3)->toDateTimeString(),
        'capacity' => 40,
        'status' => 'Published',
    ]);

    $updateResponse->assertOk()->assertJsonPath('title', 'Updated Community Workshop');

    $deleteResponse = $this->deleteJson('/api/events/'.$eventId);
    $deleteResponse->assertNoContent();

    $this->assertDatabaseMissing('events', ['id' => $eventId]);
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

test('event api supports partial updates with patch', function () {
    $event = Event::factory()->create(['title' => 'Original event']);

    $response = $this->patchJson('/api/events/'.$event->id, [
        'title' => 'Patched event',
    ]);

    $response->assertOk()
        ->assertJsonPath('title', 'Patched event')
        ->assertJsonPath('description', $event->description);
});

test('user and student api resources support partial patch updates', function () {
    Storage::fake('public');
    $user = User::factory()->create(['role' => 'Staff']);
    $student = User::factory()->create(['role' => 'Student']);

    $this->patch('/api/users/'.$user->id, ['name' => 'Updated staff'])
        ->assertRedirect(route('users.index', absolute: false));
    $this->patch('/api/students/'.$student->id, ['name' => 'Updated student'])
        ->assertRedirect(route('students.index', absolute: false));

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated staff']);
    $this->assertDatabaseHas('users', ['id' => $student->id, 'name' => 'Updated student']);
});
