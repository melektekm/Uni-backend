<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        Course::create([
            'course_code' => 'CS101',
            'course_name' => 'Introduction to Computer Science',
            'course_description' => 'An introductory course to Computer Science',
            'credit_hours' => 3,
            'year' => 1,
            'semester' => 1,
        ]);

        // Add more courses if necessary
    }
}
