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
        Schema::table('zones', function (Blueprint $table) {
            $table->json('names')->nullable()->default(null);
        });

        Schema::table('mobs', function (Blueprint $table) {
            $table->json('names')->nullable()->default(null);
        });

        Schema::table('aetherytes', function (Blueprint $table) {
            $table->json('names')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('names');
        });
        Schema::table('mobs', function (Blueprint $table) {
            $table->dropColumn('names');
        });
        Schema::table('aetherytes', function (Blueprint $table) {
            $table->dropColumn('names');
        });
    }
};
