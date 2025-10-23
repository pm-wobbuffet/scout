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
        Schema::table('scout_custom_points', function (Blueprint $table) {
            $table->bigInteger("internal_id")->nullable()->default(null)->index();
            $table->index(['scout_id', 'internal_id'], 'scout_points_scout_id_int_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scout_custom_points', function (Blueprint $table) {
            $table->dropColumn("internal_id");
            $table->dropIndex('scout_points_scout_id_int_id_idx');
        });
    }
};
