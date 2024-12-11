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
        Schema::table('despacho_ventas', function (Blueprint $table) {
            $table->decimal('despacho_venta_total_volumen',15,2)->nullable()->after('despacho_venta_total_kg');
            $table->string('despacho_venta_direccion_llegada')->nullable()->after('despacho_venta_total_volumen');
            $table->string('despacho_venta_departamento')->nullable()->after('despacho_venta_direccion_llegada');
            $table->string('despacho_venta_provincia')->nullable()->after('despacho_venta_departamento');
            $table->string('despacho_venta_distrito')->nullable()->after('despacho_venta_provincia');
            $table->tinyInteger('despacho_detalle_estado_entrega')->nullable()->after('despacho_detalle_microtime')->comment('0 Pendiente, 1 En tránsito, 2 Entregado, 3 No entregado, 4 Rechazado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despacho_ventas', function (Blueprint $table) {
            //
        });
    }
};
