<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = User::query()->firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Event Staff',
                'role' => 'Staff',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $events = [
            [
                'title' => 'Campus Welcome Mixer',
                'location' => 'Student Center Atrium',
                'description' => 'Meet new students, explore clubs, and get connected with campus life.',
                'starts_at' => now()->addDays(3)->setTime(10, 0),
                'ends_at' => now()->addDays(3)->setTime(12, 30),
                'capacity' => 120,
                'status' => 'Published',
            ],
            [
                'title' => 'Design Thinking Workshop',
                'location' => 'Innovation Lab',
                'description' => 'A hands-on session focused on problem solving, ideation, and rapid prototyping.',
                'starts_at' => now()->addDays(5)->setTime(13, 0),
                'ends_at' => now()->addDays(5)->setTime(15, 0),
                'capacity' => 50,
                'status' => 'Published',
            ],
            [
                'title' => 'Career Mentorship Circle',
                'location' => 'Career Services Office',
                'description' => 'Listen to alumni and professionals share advice on internships, resumes, and interviews.',
                'starts_at' => now()->addDays(7)->setTime(9, 30),
                'ends_at' => now()->addDays(7)->setTime(11, 30),
                'capacity' => 80,
                'status' => 'Published',
            ],
            [
                'title' => 'Open Mic Night',
                'location' => 'The Commons',
                'description' => 'Students perform poetry, songs, and spoken word in a relaxed evening showcase.',
                'starts_at' => now()->addDays(9)->setTime(18, 0),
                'ends_at' => now()->addDays(9)->setTime(20, 30),
                'capacity' => 100,
                'status' => 'Published',
            ],
            [
                'title' => 'Startup Pitch Competition',
                'location' => 'Business Hall',
                'description' => 'Student founders pitch early-stage ideas to mentors, supporters, and judges.',
                'starts_at' => now()->addDays(11)->setTime(14, 0),
                'ends_at' => now()->addDays(11)->setTime(17, 0),
                'capacity' => 150,
                'status' => 'Published',
            ],
            [
                'title' => 'Wellness and Study Break',
                'location' => 'Green Courtyard',
                'description' => 'Take a break from exams and enjoy guided mindfulness, snacks, and community activities.',
                'starts_at' => now()->addDays(13)->setTime(12, 0),
                'ends_at' => now()->addDays(13)->setTime(14, 0),
                'capacity' => 70,
                'status' => 'Published',
            ],
            [
                'title' => 'Leadership Seminar',
                'location' => 'Conference Room B',
                'description' => 'A practical seminar on communication, decision-making, and team leadership.',
                'starts_at' => now()->addDays(15)->setTime(11, 0),
                'ends_at' => now()->addDays(15)->setTime(13, 0),
                'capacity' => 60,
                'status' => 'Draft',
            ],
            [
                'title' => 'Hackathon Sprint',
                'location' => 'Tech Hub',
                'description' => 'A collaborative coding challenge where teams build, test, and pitch digital solutions.',
                'starts_at' => now()->addDays(18)->setTime(9, 0),
                'ends_at' => now()->addDays(18)->setTime(18, 0),
                'capacity' => 200,
                'status' => 'Published',
            ],
            [
                'title' => 'Volunteer Day',
                'location' => 'Community Outreach Center',
                'description' => 'Join a community service activity that supports local outreach and social impact.',
                'starts_at' => now()->addDays(20)->setTime(8, 30),
                'ends_at' => now()->addDays(20)->setTime(12, 0),
                'capacity' => 90,
                'status' => 'Published',
            ],
            [
                'title' => 'Finals Prep Lounge',
                'location' => 'Library Study Annex',
                'description' => 'A guided study and wellness session to help students prepare for finals week.',
                'starts_at' => now()->addDays(22)->setTime(16, 0),
                'ends_at' => now()->addDays(22)->setTime(19, 0),
                'capacity' => 75,
                'status' => 'Draft',
            ],
        ];

        foreach ($events as $event) {
            Event::query()->firstOrCreate(
                ['title' => $event['title'], 'starts_at' => $event['starts_at']],
                [
                    'created_by' => $staff->id,
                    'slug' => str($event['title'])->slug()->toString(),
                    'description' => $event['description'],
                    'location' => $event['location'],
                    'ends_at' => $event['ends_at'],
                    'capacity' => $event['capacity'],
                    'status' => $event['status'],
                ]
            );
        }
    }
}
