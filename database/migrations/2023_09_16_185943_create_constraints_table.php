<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConstraintsTable extends Migration
{
  

    public function up()
    {
        Schema::create('constraints', function (Blueprint $table) {
            $table->id();
            $table->string('constraint_name');
            $table->integer('max_num')->nullable();
            $table->integer('min_num')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('isclosed')->default(false);
            $table->timestamps();

        });
 // Insert predefined rows
 DB::table('constraints')->insert([
    [
        'constraint_name' => 'EmployeeBreakfastOrderMaxAmount',
        'max_num' => 1,
        'min_num' => 0,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
       
    ],
    [
        'constraint_name' => 'GuestBreakfastOrderMaxAmount',
        'max_num' => 1,
        'min_num' => 0,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
      
    ],
    [
        'constraint_name' => 'EmployeeLunchOrderMaxAmount',
        'max_num' => 1,
        'min_num' => 0,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
       
    ],
    [
        'constraint_name' => 'GuestLunchOrderMaxAmount',
        'max_num' => 1,
        'min_num' => 0,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
      
    ],
    [
        'constraint_name' => 'BreakfastOrderTime',
        'max_num' => null,
        'min_num' => null,
        'start_time' => '8:00',
        'end_time' => '11:00',
        'isclosed' => false,
       
    ],
    [
        'constraint_name' => 'LunchOrderTime',
        'max_num' => null,
        'min_num' => null,
        'start_time' => '12:30',
        'end_time' => '13:30',
        'isclosed' => false,
       
    ],
    [
        'constraint_name' => 'OrderOpened',
        'max_num' => null,
        'min_num' => null,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
      
    ],
    [
        'constraint_name' => 'todays',
        'max_num' => null,
        'min_num' => null,
        'start_time' => null,
        'end_time' => null,
        'isclosed' => false,
      
    ],
    
]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constraints');
    }
}
