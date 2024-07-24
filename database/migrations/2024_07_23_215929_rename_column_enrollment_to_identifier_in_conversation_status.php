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
        Schema::table('conversation_status', function (Blueprint $table) {
            $table->renameColumn('enrollment', 'identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_status', function (Blueprint $table) {
            $table->renameColumn('identifier', 'enrollment');
        });
    }
};
