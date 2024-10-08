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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_transportistas')->references('id_transportistas')->on('transportistas');
            $table->foreignId('id_tipo_vehiculo')->references('id_tipo_vehiculo')->on('tipo_vehiculos');
            $table->string('vehiculo_placa');
            $table->decimal('vehiculo_capacidad_peso', 10,2);
            $table->decimal('vehiculo_capacidad_volumen', 10,2);
            $table->tinyInteger('vehiculo_estado');
            $table->string('vehiculo_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
