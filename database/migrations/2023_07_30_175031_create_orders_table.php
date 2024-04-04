<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->decimal('total_price', 8, 2);
            $table->string('coupon_code');
            $table->string('status')->default('UnServed');
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('cashier_id')->references('id')->on('employees')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
          // $table->dropForeign(['special_user_id']);
            //$table->dropColumn('special_user_id');
        });
    }

}
