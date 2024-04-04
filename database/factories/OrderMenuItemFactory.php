<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderMenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' =>  Order::all()->random()->id,
            'menu_item_id' =>  MenuItem::all()->random()->id,
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
