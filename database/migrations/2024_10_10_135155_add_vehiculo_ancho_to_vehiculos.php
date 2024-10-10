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
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->decimal('vehiculo_ancho',10,2)->nullable()->after('vehiculo_capacidad_peso');
            $table->decimal('vehiculo_largo',10,2)->nullable()->after('vehiculo_ancho');
            $table->decimal('vehiculo_alto',10,2)->nullable()->after('vehiculo_largo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            //
        });
    }
};
