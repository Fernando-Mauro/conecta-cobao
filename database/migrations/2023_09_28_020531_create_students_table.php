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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('enrollment')->unique()->nullable();
            // $table->string('name');
            $table->string('phone');

            $table->boolean('active')->default(true);
            $table->string('curp')->unique();
            $table->unsignedBigInteger('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
