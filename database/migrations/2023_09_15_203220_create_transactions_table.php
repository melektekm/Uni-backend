<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{

    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->enum('type', ['deposit', 'withdrawal', 'order', 'refund','guestOrder','departmentOrder']);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
