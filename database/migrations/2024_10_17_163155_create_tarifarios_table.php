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
        Schema::create('tarifarios', function (Blueprint $table) {
            $table->id('id_tarifario');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_transportistas')->references('id_transportistas')->on('transportistas');
            $table->foreignId('id_tipo_servicio')->references('id_tipo_servicios')->on('tipo_servicios');
            $table->foreignId('id_ubigeo_salida')->nullable()->references('id_ubigeo')->on('ubigeos');
            $table->foreignId('id_ubigeo_llegada')->nullable()->references('id_ubigeo')->on('ubigeos');
            $table->decimal('tarifa_cap_min', 10,2);
            $table->decimal('tarifa_cap_max', 10,2);
            $table->decimal('tarifa_monto', 10,2);
            $table->string('tarifa_tipo_bulto');
            $table->tinyInteger('tarifa_estado');
            $table->string('tarifa_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifarios');
    }
};
