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
            \DB::unprepared('ALTER TABLE scouts DROP CHECK scouts_chk_3');
            $table->renameColumn('custom_points', 'old_custom_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scouts', function (Blueprint $table) {
            $table->renameColumn('old_custom_points', 'custom_points');
        });
    }
};
