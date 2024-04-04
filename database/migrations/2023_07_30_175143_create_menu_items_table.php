<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->default('');
            $table->string('image_url')->nullable();
            $table->decimal('price_for_guest', 8, 2);
            $table->decimal('price_for_employee', 8, 2);
            $table->string('meal_type'); // breakfast, lunch , dinner
            $table->boolean('is_fasting');
            $table->boolean('is_drink')->nullable();
            $table->boolean('is_available');
            $table->integer('available_amount');
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
        Schema::dropIfExists('menu_items');
    }
}
