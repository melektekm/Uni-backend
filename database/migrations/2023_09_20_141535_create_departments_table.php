<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
 
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();


            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
        });

        // Insert the default company department ('Min (the company)')
        DB::table('departments')->insert([
            'name' => 'የኢኖቬሽንና ቴክኖሎጂ ሚኒስቴር', 
            'parent_id' => null, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
