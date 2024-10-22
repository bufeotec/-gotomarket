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
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id('id_transportistas');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_ubigeo')->nullable()->references('id_ubigeo')->on('ubigeos');
            $table->string('transportista_ruc');
            $table->string('transportista_razon_social');
            $table->string('transportista_nom_comercial');
            $table->string('transportista_direccion')->nullable();
            $table->string('transportista_correo')->nullable();
            $table->string('transportista_telefono')->nullable();
            $table->string('transportista_contacto')->nullable();
            $table->string('transportista_cargo')->nullable();
            $table->tinyInteger('transportista_estado');
            $table->string('transportista_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};
