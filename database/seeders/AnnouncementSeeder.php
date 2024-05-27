<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Announcement::create([
            'title' => 'First Announcement',
            'content' => 'This is the content of the first announcement.',
            'category' => 'registration',
            'date' => '2024-05-01',
            'file_path' => null,
        ]);

        Announcement::create([
            'title' => 'Second Announcement',
            'content' => 'This is the content of the second announcement.',
            'category' => 'upcoming_events',
            'date' => '2024-05-15',
            'file_path' => null,
        ]);
    }
}
