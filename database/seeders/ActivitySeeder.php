<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            'Swimming',
            'Diving',
            'Surfing',
            'Fishing',
            'Mountain Climbing',
            'Paragliding',
            'Skydiving',
            'Traditional Dance',
            'Art Workshops',
            'Yoga Classes',
            'Nature Tours',
            'Historical Tours',
            'Tennis Coaching',
            'Golf',
            'Music Lessons',
            'Dance Classes',
            'DJ Services',
            'Tour Guide',
            'Photography',
            'Videography',
            'Translation'
        ];

        foreach ($activities as $activity) {
            Activity::query()->create([
                'name' => $activity
            ]);
        }
    }
}
