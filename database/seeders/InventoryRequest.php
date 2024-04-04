<?php

namespace Database\Seeders;

use App\Models\inventoryRequest as ModelsInventoryRequest;
use Illuminate\Database\Seeder;

class InventoryRequest extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ModelsInventoryRequest::factory()->count(100)->create();
    }
}