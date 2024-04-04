<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'department' => $this->faker->word,
            'position' => $this->faker->jobTitle,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'role' => 'employee',
        ];
    }
}
