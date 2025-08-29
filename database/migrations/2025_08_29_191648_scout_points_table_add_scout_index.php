<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scout_points', function (Blueprint $table) {
            $table->index('scout_id', 'scout_points_scout_id_idx');
            $table->string('reporter', 40)->nullable()->default(null)->after('y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scout_points', function (Blueprint $table) {
            $table->dropIndex('scout_points_scout_id_idx');
            $table->dropColumn('reporter');
        });
    }
};
