<?php

namespace Database\Seeders;

use App\Models\OrderMenuItem;
use Illuminate\Database\Seeder;

class OrderMenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderMenuItem::factory()->count(15)->create();
    }
}
