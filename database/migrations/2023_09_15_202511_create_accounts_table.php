<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->decimal('balance', 10, 2);
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->date('last_deposit_date')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
