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
            $table->foreignId('id_transportistas')->nullable()->references('id_transportistas')->on('transportistas');
            $table->foreignId('id_vehiculo')->nullable()->references('id_vehiculo')->on('vehiculos');
            $table->foreignId('id_departamento')->nullable()->references('id_departamento')->on('departamentos');
            $table->foreignId('id_provincia')->nullable()->references('id_provincia')->on('provincias');
            $table->foreignId('id_distrito')->nullable()->references('id_distrito')->on('distritos');
            $table->dateTime('despacho_fecha');
            $table->tinyInteger('despacho_tipo')->comment('local es 1, provincial es 2');
            $table->decimal('despacho_peso_total', 10,2);
            $table->decimal('despacho_costo_total', 10,2);
            $table->string('despacho_mano_obra')->nullable();
            $table->string('despacho_otro')->nullable();
            $table->tinyInteger('despacho_estado')->comment('pendiente es 1, completado es 2, cancelado es 3');
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
