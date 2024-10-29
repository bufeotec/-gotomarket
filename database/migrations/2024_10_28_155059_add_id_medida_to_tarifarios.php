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
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->foreignId('id_medida')->nullable()->after('id_ubigeo_llegada')->references('id_medida')->on('medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            //
        });
    }
};
