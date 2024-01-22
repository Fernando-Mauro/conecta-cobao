<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained(); // Relaciona con la tabla students
            $table->string('tutor_email');
            $table->string('document_url');
            $table->foreignId('tutor_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true); // Nuevo campo para indicar si está activo
            $table->boolean('is_approved')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('justifications');
    }
};
