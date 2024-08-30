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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            
            // If is like secondary, the name is: 'A' if is like high school, the name is: 303
            $table->integer('name');
            $table->unsignedBigInteger('campus_id');
            $table->foreign('campus_id')->references('id')->on('campus');

            $table->unsignedBigInteger('level_id');
            $table->foreign('level_id')->references('id')->on('levels');

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
