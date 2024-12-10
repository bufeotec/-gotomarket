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
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id('id_liquidacion');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_transportistas')->references('id_transportistas')->on('transportistas');
            $table->string('liquidacion_serie')->comment('serie del comprobante');
            $table->string('liquidacion_correlativo')->comment('correlativo del comprobante');
            $table->string('liquidacion_ruta_comprobante')->comment('ruta del archivo del comprobante almacenado')->nullable();
            $table->tinyInteger('liquidacion_estado')->comment('0 es desactivado, 1 es activo');
            $table->string('liquidacion_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacions');
    }
};
