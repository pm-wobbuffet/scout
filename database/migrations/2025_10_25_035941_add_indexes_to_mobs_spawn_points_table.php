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
        Schema::table('mobs_spawn_points', function (Blueprint $table) {
            $table->index('mob_id');
            $table->index('spawn_point_id');
        });

        Schema::table('spawn_points', function (Blueprint $table) {
            $table->index('zone_id');
        });

        Schema::table('aetherytes', function (Blueprint $table) {
            $table->index('zone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobs_spawn_points', function (Blueprint $table) {
            $table->dropIndex('mobs_spawn_points_mob_id_index');
            $table->dropIndex('mobs_spawn_points_spawn_point_id_index');
        });

        Schema::table('spawn_points', function (Blueprint $table) {
            $table->dropIndex('spawn_points_zone_id_index');
        });

        Schema::table('aetherytes', function (Blueprint $table) {
            $table->dropIndex('aetherytes_zone_id_index');
        });
    }
};
