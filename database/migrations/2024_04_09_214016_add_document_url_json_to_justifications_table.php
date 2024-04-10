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
        Schema::table('justifications', function (Blueprint $table) {
            // Crear una nueva columna para almacenar los datos en formato JSON
            $table->json('document_url_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('justifications', function (Blueprint $table) {
            // Eliminar la columna 'document_url_json'
            $table->dropColumn('document_url_json');
        });
    }
};
