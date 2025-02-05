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
        Schema::create('tarifas_movil', function (Blueprint $table) {
            $table->id('id_tarifa_movil');
            $table->foreignId('id_users')->constrained('users', 'id_users');
            $table->foreignId('id_tarifario')->constrained('tarifarios', 'id_tarifario');
            $table->foreignId('id_vehiculo')->constrained('vehiculos', 'id_vehiculo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas_movil');
    }
};
