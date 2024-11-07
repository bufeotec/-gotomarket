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
        Schema::create('despacho_ventas', function (Blueprint $table) {
            $table->id('id_despacho_venta');

            $table->foreignId('id_despacho')->nullable()->references('id_despacho')->on('despachos');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despacho_ventas');
    }
};
