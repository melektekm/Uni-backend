<?php

namespace Database\Seeders;

use App\Models\stockResquest as ModelsstockResquest;
use Illuminate\Database\Seeder;

class stockRequestseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ModelsstockResquest::factory()->count(50)->create();
    }
}