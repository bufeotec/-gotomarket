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
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('id_menu')->after('id')->nullable()->references('id_menu')->on('menus');
            $table->foreignId('id_submenu')->after('id_menu')->nullable()->references('id_submenu')->on('submenus');
            $table->tinyInteger('permissions_group')->after('guard_name')->comment('1 Menu -  2 Submenu - 3 Functions');
            $table->bigInteger('permissions_group_id')->after('permissions_group')->comment('ID DE MENU - SUBMENU - FUNCTION');
            $table->tinyInteger('permission_status')->after('permissions_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });
    }
};
