<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\SystemUserEmployee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $couponCode = Str::random(8);
        return [
            'employee_id' =>  Employee::all()->random()->id,
            'total_price' => $this->faker->randomFloat(2, 1, 100),
            'coupon_code' => $couponCode
        ];
    }
}
