<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('_assignment', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('course_id');
            // $table->unsignedBigInteger('department');
            $table->string('ass_name');
            $table->string('Add_description')->nullable();
            $table->string('due_date');
            $table->string('file_path')->nullable();
            $table->string('status')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_assignments');
    }
}
