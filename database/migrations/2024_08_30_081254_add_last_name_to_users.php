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
            $table->string('last_name')->nullable()->after('name');
            $table->string('users_token')->nullable()->after('remember_token');
            $table->dateTime('users_token_time')->nullable()->after('users_token');
            $table->tinyInteger('users_status')->default(1)->comment('1 Activo - 0 Desactivado')->nullable()->after('users_token_time');
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
