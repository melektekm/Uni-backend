<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('measured_in')->nullable();
            $table->decimal('quantity');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->foreign('requested_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('inventory_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_requests');
    }
}
