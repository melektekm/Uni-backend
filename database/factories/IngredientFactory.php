<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [

            'name' => $this->faker->word,
            'quantity' => $this->faker->numberBetween(1, 100),
            'measuredIn' => $this->faker->word,
            'itemPrice' => $this->faker->randomFloat(2, 0, 100),
            'approvedBy' => $this->faker->name,
        ];
    }
}