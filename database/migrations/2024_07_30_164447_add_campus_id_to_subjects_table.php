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
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('campus_id');

            $table->foreign('campus_id')
                ->references('id')
                ->on('campus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // First delete the relation
            $table->dropForeign(['campus_id']);

            // Then delete the column
            $table->dropColumn('campus_id');
        });
    }
};
