<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_orders', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->string('file_path')->nullable();
            $table->decimal('lunch_price_per_person', 8, 2)->nullable();
            $table->decimal('refreshment_price_per_person', 8, 2)->nullable();
            $table->unsignedBigInteger('refreshment_per_day')->nullable();
            $table->unsignedBigInteger('number_of_people');
            $table->unsignedBigInteger('number_of_days');
            $table->date('serving_date_start')->nullable();
            $table->date('serving_date_end')->nullable();
            $table->unsignedBigInteger('buyer_id');
            $table->timestamps();
        });
        Schema::table('department_orders', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department_orders');
    }
}
