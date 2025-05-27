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
        Schema::create('scout_zone_instance_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts');
            $table->foreignId('zone_id')->references('id')->on('zones');
            $table->unsignedInteger('instance_count')->default(1);
            $table->timestamps();

            $table->unique(['scout_id', 'zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_zone_instance_counts');
    }
};
