<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_request_entries', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('purchase_request_start_id')->nullable();
    $table->unsignedBigInteger('purchase_request_end_id')->nullable();
    $table->unsignedBigInteger('submitted_items_start_id')->nullable();
    $table->unsignedBigInteger('submitted_items_end_id')->nullable();
    $table->string('recommendations')->nullable();
    $table->unsignedBigInteger('request_approved_by')->nullable();
    $table->unsignedBigInteger('entry_approved_by')->nullable();
    $table->unsignedBigInteger('requested_by')->nullable();
    $table->decimal('total_price_request')->nullable();
    $table->decimal('total_price_entry')->nullable();
    $table->decimal('returned_amount')->nullable();
    $table->string('request_status')->nullable();
    $table->string('file_path')->nullable();;

    $table->timestamps();
        });

        Schema::table('inventory_request_entries', function (Blueprint $table) {
            $table->foreign('request_approved_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('entry_approved_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('purchase_request_start_id')->references('id')->on('inventory_requests')->onDelete('cascade');
            $table->foreign('purchase_request_end_id')->references('id')->on('inventory_requests')->onDelete('cascade');
            $table->foreign('submitted_items_start_id')->references('id')->on('inventory_entries')->onDelete('cascade');
            $table->foreign('submitted_items_end_id')->references('id')->on('inventory_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_request_entries');
    }
}
