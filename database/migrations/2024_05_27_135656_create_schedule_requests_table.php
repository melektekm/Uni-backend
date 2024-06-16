<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->string('course_code');
            $table->string('course_name');
            $table->string('classroom');
            $table->string('labroom')->nullable();
            $table->string('classDays');
            $table->string('labDays')->nullable();
            $table->string('labInstructor')->nullable();
            $table->string('classInstructor');
            $table->enum('schedule_type', ['Exam', 'Class']);
            $table->timestamps();

            $table->foreign('course_code')->references('course_code')->on('courses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_requests');
    }
}
