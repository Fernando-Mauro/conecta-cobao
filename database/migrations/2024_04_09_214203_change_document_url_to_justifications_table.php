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
            // Migrar los datos de la columna 'document_url' a la nueva columna 'document_url_json'
            DB::table('justifications')->get()->each(function ($justification) {
                DB::table('justifications')
                    ->where('id', $justification->id)
                    ->update(['document_url_json' => json_encode($justification->document_url)]);
            });

            // Eliminar la columna 'document_url'
            $table->dropColumn('document_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('justifications', function (Blueprint $table) {
            // Crear la columna 'document_url' de nuevo
            $table->string('document_url')->nullable();

            // Migrar los datos de la columna 'document_url_json' a la columna 'document_url'
            DB::table('justifications')->get()->each(function ($justification) {
                DB::table('justifications')
                    ->where('id', $justification->id)
                    ->update(['document_url' => json_decode($justification->document_url_json)]);
            });

            // Eliminar la columna 'document_url_json'
            // $table->dropColumn('document_url_json');
        });
    }
};
