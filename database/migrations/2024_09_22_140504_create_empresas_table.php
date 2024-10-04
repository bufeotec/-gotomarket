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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id('id_empresa');
            $table->integer('empresa_ruc');
            $table->foreignId('id_ubigeo')->references('id_ubigeo')->on('ubigeos');
            $table->string('empresa_razon_social');
            $table->string('empresa_domicilio_fiscal')->nullable();
            $table->string('empresa_nombre_comercial');
            $table->string('empresa_telefono_uno')->nullable();
            $table->string('empresa_telefono_dos')->nullable();
            $table->string('empresa_email_uno')->nullable();
            $table->string('empresa_email_dos')->nullable();
            $table->string('empresa_descricion')->nullable();
            $table->string('empresa_usuario')->nullable();
            $table->string('empresa_clave')->nullable();
            $table->string('empresa_archivo')->nullable();
            $table->string('empresa_clave_certificado')->nullable();
            $table->string('empresa_logo')->nullable();
            $table->tinyInteger('empresa_estado_produccion')->comment('0 test, 1 produccion');
            $table->tinyInteger('empresa_estado_boleta')->comment('1 Resumen Diario, 2 Envío Directo');
            $table->tinyInteger('empresa_estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
