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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('users_dni')->after('users_status')->comment('DNI del usuario opcional')->nullable();
            $table->integer('users_phone')->after('users_dni')->comment('Teléfono del usuario opcional')->nullable();
            $table->integer('users_birthdate')->after('users_phone')->comment('Fecha de nacimiento del usuario opcional')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
