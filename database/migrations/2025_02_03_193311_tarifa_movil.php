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
        Schema::create('tarifa_movil', function (Blueprint $table) {
            $table->id('id_tarifa_movil');
            $table->foreignId('id_users')->constrained('users', 'id_users');
            $table->foreignId('id_tarifario')->constrained('tarifarios', 'id_tarifario');
            $table->tinyInteger('tarifa_movil_estado')->nullable()->comment('0 No aprovado, 2 Calidad);');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
