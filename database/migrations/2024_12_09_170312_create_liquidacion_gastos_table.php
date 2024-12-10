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
        Schema::create('liquidacion_gastos', function (Blueprint $table) {
            $table->id('id_liquidacion_gasto');
            $table->foreignId('id_liquidacion_detalle')->references('id_liquidacion_detalle')->on('liquidacion_detalles');
            $table->string('liquidacion_gasto_concepto')->comment('Concepto breve que identifica de forma clara el tipo de gasto generado');
            $table->decimal('liquidacion_gasto_monto',15,2)->comment('Monto total del gasto registrado');
            $table->string('liquidacion_gasto_descripcion')->comment('Descripción detallada y opcional para especificar información adicional sobre el gasto.')->nullable();
            $table->tinyInteger('liquidacion_gasto_estado')->comment('0 es desactivado, 1 es activo');
            $table->string('liquidacion_gasto_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacion_gastos');
    }
};
