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
            $table->decimal('despacho_monto_modificado',15,2)->nullable()->after('despacho_descripcion_otros');
            $table->tinyInteger('despacho_estado_modificado')->nullable()->after('despacho_monto_modificado');
            $table->string('despacho_descripcion_modificado')->nullable()->after('despacho_estado_modificado');
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
