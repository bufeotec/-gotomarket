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
            $table->dateTime('despacho_venta_grefecemision')->nullable()->after('despacho_venta_factura');
            $table->string('despacho_venta_cnomcli')->nullable()->after('despacho_venta_grefecemision');
            $table->string('despacho_venta_guia')->nullable()->after('despacho_venta_cnomcli');
            $table->string('despacho_venta_cfimporte')->nullable()->after('despacho_venta_guia');
            $table->decimal('despacho_venta_total_kg',15,2)->nullable()->after('despacho_venta_cfimporte');
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
