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
        Schema::create('menus', function (Blueprint $table) {
            $table->id('id_menu');
            $table->string('menu_name')->comment('Nombre del menú');
            $table->string('menu_controller')->comment('Nombre del controlador asociado al menú');
            $table->string('menu_icons')->comment('Clase CSS del icono del menú');
            $table->integer('menu_order')->comment('Posición de orden del menú en la interfaz');
            $table->tinyInteger('menu_show')->comment('Indica si el menú se muestra (1) o no se muestra (0)');
            $table->tinyInteger('menu_status')->comment('Estado del menú: 1 para activo, 0 para inactivo');
            $table->string('menu_microtime')->comment('Marca de tiempo única para el menú');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
