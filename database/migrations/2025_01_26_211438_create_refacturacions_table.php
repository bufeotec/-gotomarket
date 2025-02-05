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
        Schema::create('refacturaciones', function (Blueprint $table) {
            $table->id('id_refacturacion');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->foreignId('id_despacho_venta')->references('id_despacho_venta')->on('despacho_ventas');
            $table->string('refacturacion_cftd');
            $table->string('refacturacion_cfnumser');
            $table->string('refacturacion_cfnumdoc');
            $table->string('refacturacion_factura');
            $table->dateTime('refacturacion_grefecemision');
            $table->string('refacturacion_cnomcli');
            $table->string('refacturacion_cfcodcli');
            $table->string('refacturacion_guia');
            $table->string('refacturacion_cfimporte');
            $table->decimal('refacturacion_total_kg', 15,2);
            $table->decimal('refacturacion_total_volumen', 15,2);
            $table->string('refacturacion_direccion_llegada');
            $table->string('refacturacion_departamento');
            $table->string('refacturacion_provincia');
            $table->string('refacturacion_distrito');
            $table->tinyInteger('refacturacion_estado')->comment('0 es desactivado, 1 es activo');
            $table->string('refacturacion_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refacturaciones');
    }
};
