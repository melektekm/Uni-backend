<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
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
            'description' => $this->faker->sentence,
            'image_url' => $this->faker->imageUrl,
            'price_for_guest' => $this->faker->randomFloat(2, 1, 100),
            'price_for_employee' => $this->faker->randomFloat(2, 1, 100),
            'meal_type' => $this->faker->randomElement(['breakfast', 'lunch', 'both']),
            'is_fasting' => $this->faker->boolean,
            'is_drink' => $this->faker->boolean,
            'available_amount'=>$this->faker->randomInt(),
            'is_available' => $this->faker->boolean,

        ];
    }
}
