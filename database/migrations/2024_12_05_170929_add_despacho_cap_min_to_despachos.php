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
            $table->string('despacho_cap_min')->nullable()->after('despacho_microtime')->comment('Este campo guarda la capacidad minima del id_tarifairo seleccionado');
            $table->string('despacho_cap_max')->nullable()->after('despacho_cap_min')->comment('Este campo guarda la capacidad maxima del id_tarifairo seleccionado');
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
