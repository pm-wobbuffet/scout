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
        Schema::table('mobs', function (Blueprint $table) {
            $table->index('rank', 'mobs_mob_rank_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobs', function (Blueprint $table) {
            $table->dropIndex('mobs_mob_rank_idx');
        });
    }
};
