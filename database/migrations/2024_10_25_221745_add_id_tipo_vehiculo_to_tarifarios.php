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
            $table->foreignId('id_tipo_vehiculo')->nullable()->after('id_tipo_servicio')->references('id_tipo_vehiculo')->on('tipo_vehiculos');
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
