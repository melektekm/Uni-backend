<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('measured_in')->nullable();
            $table->decimal('quantity');
            $table->decimal('price_per_item');
            $table->string('price_word')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_requests');
    }
}
