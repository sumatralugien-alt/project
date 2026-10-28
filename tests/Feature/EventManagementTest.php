<?php

use App\Models\Event;
use App\Models\User;

test('staff can create and manage events', function () {
    $staff = User::factory()->create(['role' => 'Staff']);

    $response = $this->actingAs($staff)->post(route('events.store'), [
        'title' => 'Campus Innovation Day',
        'description' => 'A day for student projects.',
        'location' => 'Main Hall',
        'starts_at' => '2026-10-10 09:00',
        'ends_at' => '2026-10-10 16:00',
        'capacity' => 100,
        'status' => 'Published',
    ]);

    $response->assertRedirect(route('events.index', absolute: false));
    $this->assertDatabaseHas('events', ['title' => 'Campus Innovation Day', 'created_by' => $staff->id]);
});

test('students cannot access staff management screens', function () {
    $student = User::factory()->create(['role' => 'Student']);

    $this->actingAs($student)->get(route('events.create'))->assertForbidden();
    $this->actingAs($student)->get(route('students.index'))->assertForbidden();
});

test('students see a view action when browsing events', function () {
    $student = User::factory()->create(['role' => 'Student']);
    $event = Event::factory()->create(['status' => 'Published']);

    $response = $this->actingAs($student)->get(route('events.index'));

    $response->assertOk();
    $response->assertSee('>View</a>', false);
    $response->assertSee(route('events.show', $event, absolute: false), false);
});

test('events can be retrieved as json', function () {
    $staff = User::factory()->create(['role' => 'Staff']);
    Event::factory()->create(['title' => 'Postman Event']);

    $response = $this->actingAs($staff)->getJson(route('events.index'));

    $response->assertOk();
    $response->assertJsonPath('data.0.title', 'Postman Event');
});

test('students can register for a published event and cancel it', function () {
    $student = User::factory()->create(['role' => 'Student']);
    $event = Event::factory()->create([
        'created_by' => User::factory()->create(['role' => 'Staff'])->id,
        'status' => 'Published',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    $this->actingAs($student)->post(route('registrations.store', $event))->assertRedirect(route('events.show', $event, absolute: false));
    $this->assertDatabaseHas('event_registrations', ['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Registered']);

    $registration = $student->registrations()->first();
    $registrationsResponse = $this->actingAs($student)->get(route('registrations.index'));
    $registrationsResponse->assertOk();
    $registrationsResponse->assertSee('Cancel registration');
    $registrationsResponse->assertSee(route('registrations.destroy', $registration, absolute: false), false);

    $this->actingAs($student)->patch(route('registrations.destroy', $registration))->assertRedirect(route('registrations.index', absolute: false));
    $this->assertDatabaseHas('event_registrations', ['id' => $registration->id, 'status' => 'Cancelled']);

    $cancelledResponse = $this->actingAs($student)->get(route('registrations.index'));
    $cancelledResponse->assertSee('Cancelled');
    $cancelledResponse->assertSee('Delete registration');
    $cancelledResponse->assertSee(route('registrations.delete', $registration, absolute: false), false);

    $this->actingAs($student)->delete(route('registrations.delete', $registration))->assertRedirect(route('registrations.index', absolute: false));
    $this->assertDatabaseMissing('event_registrations', ['id' => $registration->id]);
});

test('students cannot register for a full event', function () {
    $student = User::factory()->create(['role' => 'Student']);
    $event = Event::factory()->create(['capacity' => 1]);
    $existingStudent = User::factory()->create(['role' => 'Student']);

    $event->registrations()->create([
        'user_id' => $existingStudent->id,
        'status' => 'Registered',
        'registered_at' => now(),
    ]);

    $this->actingAs($student)
        ->post(route('registrations.store', $event))
        ->assertStatus(422);

    $this->assertDatabaseMissing('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $student->id,
    ]);
});
