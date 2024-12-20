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
        Schema::table('programaciones', function (Blueprint $table) {
            $table->foreignId('id_users_programacion')->nullable()->after('id_users')->references('id_users')->on('users');
            $table->tinyInteger('programacion_estado_aprobacion')->nullable()->after('programacion_fecha');
            $table->string('programacion_numero_correlativo')->nullable()->after('programacion_estado_aprobacion');
            $table->date('programacion_fecha_aprobacion')->nullable()->after('programacion_numero_correlativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programaciones', function (Blueprint $table) {
            //
        });
    }
};
