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
        Schema::create('liquidacion_detalles', function (Blueprint $table) {
            $table->id('id_liquidacion_detalle');
            $table->foreignId('id_liquidacion')->references('id_liquidacion')->on('liquidaciones');
            $table->foreignId('id_despacho')->references('id_despacho')->on('despachos');
            $table->tinyInteger('liquidacion_detalle_estado')->comment('0 es desactivado, 1 es activo');
            $table->string('liquidacion_detalle_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacion_detalles');
    }
};
