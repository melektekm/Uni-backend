<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_name' => $this->faker->words(3, true),
            'course_code' => strtoupper($this->faker->bothify('??###')),
            'classroom' => $this->faker->word,
            'labroom' => $this->faker->word,
            'classDays' => $this->faker->dayOfWeek,
            'labDays' => $this->faker->dayOfWeek,
            'labInstructor' => $this->faker->name,
            'classInstructor' => $this->faker->name,
            'scheduleType' => $this->faker->randomElement(['Exam', 'Class']),
            'status' => 'Pending',
        ];
    }
}
