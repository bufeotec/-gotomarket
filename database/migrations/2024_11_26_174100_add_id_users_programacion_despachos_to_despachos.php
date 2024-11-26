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
        Schema::table('despachos', function (Blueprint $table) {
            $table->foreignId('id_users_programacion')->nullable()->after('id_users')->references('id_users')->on('users');
            $table->tinyInteger('despacho_estado_aprobacion')->nullable()->after('despacho_costo_total');
            $table->string('despacho_numero_correlativo')->nullable()->after('despacho_estado_aprobacion');
            $table->date('despacho_fecha_aprobacion')->nullable()->after('despacho_numero_correlativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            //
        });
    }
};
