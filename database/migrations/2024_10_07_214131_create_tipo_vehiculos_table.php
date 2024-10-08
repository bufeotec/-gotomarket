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
        Schema::create('tipo_vehiculos', function (Blueprint $table) {
            $table->id('id_tipo_vehiculo');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->string('tipo_vehiculo_concepto');
            $table->tinyInteger('tipo_vehiculo_estado');
            $table->string('tipo_vehiculo_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_vehiculos');
    }
};
