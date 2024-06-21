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
        Schema::table('scouts', function (Blueprint $table) {
            $table->json('custom_points')->nullable()->default(null);
        });
        Schema::table('scout_updates', function (Blueprint $table) {
            $table->json('previous_custom_points')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scouts', function (Blueprint $table) {
            $table->dropColumn('custom_points');
        });
        Schema::table('scout_updates', function (Blueprint $table) {
            $table->dropColumn('previous_custom_points');
        });
    }
};
