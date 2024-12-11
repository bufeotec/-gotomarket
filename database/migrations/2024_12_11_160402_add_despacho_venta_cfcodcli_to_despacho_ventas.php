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
            $table->string('despacho_venta_cfcodcli')->nullable()->after('despacho_venta_cnomcli');
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
