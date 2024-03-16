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
        Schema::create('history_messages', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('message_id');
            $table->foreign('message_id')->references('id')->on('messages');
            
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
        Schema::table('history_messages', function (Blueprint $table) {
            $table->dropForeign(['message_id']); // Elimina la clave foránea
        });
        
        Schema::dropIfExists('history_messages');
    }
};
