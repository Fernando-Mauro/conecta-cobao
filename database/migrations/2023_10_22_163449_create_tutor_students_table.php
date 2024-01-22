<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tutor_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tutor_id');
            $table->foreign('tutor_id')->references('id')->on('tutors');
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {   
        Schema::table('tutor_students', function (Blueprint $table) {
            if(Schema::hasColumn('tutor_students', 'tutor_id')) {
                $table->dropForeign(['tutor_id']);
                $table->dropColumn('tutor_id');
            }
            if(Schema::hasColumn('tutor_students', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }
        });

        Schema::dropIfExists('tutor_students');
    }
};