<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubmittedAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submitted_assignments', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('assignment_id');
            $table->string('status')->default('unsubmitted');
            $table->timestamps();
        });
        Schema::table('subitted_assignments', function (Blueprint $table) {
            $table->foreign('id')->references('id')->on('_assignment')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subitted_assignments');

    }
}
