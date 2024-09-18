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
        Schema::table('scout_updates', function (Blueprint $table) {
            $table->index('scout_id', 'scout_updates_scout_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scout_updates', function (Blueprint $table) {
            $table->dropIndex('scout_updates_scout_id_index' );
        });
    }
};
