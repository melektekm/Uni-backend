<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class stockResquestFactory extends Factory
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
            'quantity' => $this->faker->numberBetween(1, 100),
            'measuredIn' => $this->faker->word,
            'requestedBy' => $this->faker->name,
            'is_allowed' => $this->$this->faker->randomInt(),
        ];
    }
}