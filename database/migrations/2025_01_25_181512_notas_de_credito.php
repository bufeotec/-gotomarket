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
        Schema::create('nota_creditos', function (Blueprint $table) {
            $table->id('id_nota_credito'); // Columna auto_increment y clave primaria
            $table->foreignId('id_users')->constrained('users', 'id_users');
            $table->foreignId('id_refacturacion')->nullable();
            $table->foreignId('id_despacho_venta')->constrained('despacho_ventas', 'id_despacho_venta'); // Foreign key
//            $table->date('nota_credito_fecha_emision');
            $table->string('nota_credito_ruc_cliente');
            $table->string('nota_credito_nombre_cliente');
            $table->tinyInteger('nota_credito_motivo')->nullable()->comment('1 Deuda, 2 Calidad, 3 Cobranza, 4 Error de facturación, 5 Otros comercial');
//            $table->tinyInteger('nota_credito_incidente_registro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_creditos');
    }
};
