<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function shouldRun(): bool
    {
        return boolval(config('app.scout.old_reports_migrated', false));
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scouts', function (Blueprint $table) {
            $table->dropColumn(['instance_data', 'point_data', 'old_custom_points', 'scouts_old', 'mob_status', 'occupied_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No way to undo this one buddy, better be sure you want to run it
    }
};
