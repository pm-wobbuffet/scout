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
        Schema::create('scout_custom_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts');
            $table->foreignId('zone_id')->references('id')->on('zones');
            $table->decimal('x', 3, 1)->default(0.0);
            $table->decimal('y', 3, 1)->default(0.0);
            $table->timestamps();

            // Only allow a single x, y entry for a given zone in a scout report
            $table->unique(['scout_id', 'zone_id', 'x', 'y']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_custom_points');
    }
};
