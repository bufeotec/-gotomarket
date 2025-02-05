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
        Schema::create('facturas_pre_programaciones', function (Blueprint $table) {
            $table->id('id_fac_pre_prog');
            $table->string('fac_pre_prog_cftd');
            $table->string('fac_pre_prog_cfnumser');
            $table->string('fac_pre_prog_cfnumdoc');
            $table->string('fac_pre_prog_factura');
            $table->dateTime('fac_pre_prog_grefecemision');
            $table->string('fac_pre_prog_cnomcli');
            $table->string('fac_pre_prog_cfcodcli');
            $table->string('fac_pre_prog_guia');
            $table->string('fac_pre_prog_cfimporte');
            $table->decimal('fac_pre_prog_total_kg', 15,2);
            $table->decimal('fac_pre_prog_total_volumen', 15,2);
            $table->string('fac_pre_prog_direccion_llegada');
            $table->string('fac_pre_prog_departamento');
            $table->string('fac_pre_prog_provincia');
            $table->string('fac_pre_prog_distrito');
            $table->tinyInteger('fac_pre_prog_estado_aprobacion')->comment('1 en administración, 2 en manos del despachador, 3 listo para despacho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_pre_programaciones');
    }
};
