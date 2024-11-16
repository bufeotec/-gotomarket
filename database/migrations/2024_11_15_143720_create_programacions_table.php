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
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id('id_programacion');
            $table->foreignId('id_users')->references('id_users')->on('users');
            $table->date('programacion_fecha');
            $table->tinyInteger('programacion_estado')->comment('pendiente es 1, completado es 2, cancelado es 3');
            $table->string('programacion_microtime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programacions');
    }
};
