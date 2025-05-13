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
        Schema::create('scout_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts')->onDelete('cascade');
            $table->foreignId('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->morphs('point');
            $table->unsignedInteger('instance_number')->default(1);
            $table->foreignId('mob_id')->nullable()->references('id')->on('mobs')->onDelete('cascade')->default(null);
            $table->decimal('x',3,1)->nullable()->default(null);
            $table->decimal('y',3,1)->nullable()->default(null);
            $table->timestamps();

            $table->unique(['scout_id', 'zone_id', 'point_id', 'instance_number'],'scout_points_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_points');
    }
};
