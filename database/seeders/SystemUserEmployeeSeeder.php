<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\SystemUserEmployee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUserEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $systemUser = [
            [
                'employee_id' => Employee::all()->random()->id,
            ],
            [
                'employee_id' => Employee::all()->random()->id,
            ],
            [
                'employee_id' => Employee::all()->random()->id,
            ],
        ];

        foreach ($systemUser as $user) {
            SystemUserEmployee::create($user);
        }
    }
}
