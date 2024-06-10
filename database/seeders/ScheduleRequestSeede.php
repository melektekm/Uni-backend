<?php

namespace Database\Seeders;

use App\Models\ScheduleRequest;
use Illuminate\Database\Seeder;

class ScheduleRequestSeede extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ScheduleRequest::factory()->count(10)->create();
    }
}
