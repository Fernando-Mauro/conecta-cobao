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
        Schema::table('justifications', function (Blueprint $table) {
            $table->renameColumn('document_url_json', 'files_names');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('justifications', function (Blueprint $table) {
            $table->renameColumn('files_names', 'document_url_json');
        });
    }
};
