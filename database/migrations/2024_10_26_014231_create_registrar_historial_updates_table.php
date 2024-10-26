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
        Schema::create('registrar_historial_updates', function (Blueprint $table) {
            $table->id('id_registrar');
            $table->foreignId('id_tarifario')->references('id_tarifario')->on('tarifarios');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->string('registro_concepto', 800);
            $table->dateTime('registro_hora_fecha');
            $table->tinyInteger('registro_estado');
            $table->string('registro_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrar_historial_updates');
    }
};
