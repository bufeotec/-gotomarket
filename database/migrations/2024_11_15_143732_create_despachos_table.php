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
        Schema::create('despachos', function (Blueprint $table) {
            $table->id('id_despacho');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_programacion')->nullable()->references('id_programacion')->on('programaciones');
            $table->foreignId('id_transportistas')->nullable()->references('id_transportistas')->on('transportistas');
            $table->foreignId('id_tipo_servicios')->nullable()->references('id_tipo_servicios')->on('tipo_servicios');
            $table->foreignId('id_vehiculo')->nullable()->references('id_vehiculo')->on('vehiculos');
            $table->foreignId('id_departamento')->nullable()->references('id_departamento')->on('departamentos');
            $table->foreignId('id_provincia')->nullable()->references('id_provincia')->on('provincias');
            $table->foreignId('id_distrito')->nullable()->references('id_distrito')->on('distritos');
            $table->decimal('despacho_peso', 15,2);
            $table->decimal('despacho_volumen', 15,2);
            $table->decimal('despacho_flete', 15,2);
            $table->decimal('despacho_ayudante', 15,2)->nullable();
            $table->decimal('despacho_ayudante', 15,2)->nullable();
            $table->decimal('despacho_costo_total', 15,2);
            $table->tinyInteger('despacho_estado');
            $table->string('despacho_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
