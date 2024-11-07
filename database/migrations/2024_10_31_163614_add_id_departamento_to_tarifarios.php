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
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->foreignId('id_departamento')->nullable()->after('id_ubigeo_salida')->references('id_departamento')->on('departamentos');

            $table->foreignId('id_provincia')->nullable()->after('id_departamento')->references('id_provincia')->on('provincias');

            $table->foreignId('id_distrito')->nullable()->after('id_provincia')->references('id_distrito')->on('distritos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            //
        });
    }
};
