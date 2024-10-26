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
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->tinyInteger('tarifa_estado_aprobacion')->after('tarifa_microtime')->comment('Aprobación para los tarifas donde 0 es pendiente y 1 aprobado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            //
        });
    }
};
