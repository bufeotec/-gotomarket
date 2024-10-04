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
        Schema::create('submenus', function (Blueprint $table) {
            $table->id('id_submenu');
            $table->foreignId('id_menu')->comment('Clave foránea que refiere al ID del menú padre')->references('id_menu')->on('menus');
            $table->string('submenu_name')->comment('Nombre del submenú');
            $table->string('submenu_function')->comment('Función o ruta asociada al submenú');
            $table->integer('submenu_order')->comment('Posición de orden del submenú en la interfaz');
            $table->tinyInteger('submenu_show')->comment('Indica si el submenú se muestra (1) o no se muestra (0)');
            $table->tinyInteger('submenu_status')->comment('Estado del submenú: 1 para activo, 0 para inactivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submenus');
    }
};
