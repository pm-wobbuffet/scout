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
            $table->boolean('assigned_by_import')->default(false);
            $table->index('assigned_by_import');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scout_custom_points', function (Blueprint $table) {
            $table->dropColumn('assigned_by_import');
        });
    }
};
