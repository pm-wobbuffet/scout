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
            $table->foreignId('point_id')->references('id')->on('spawn_points')->onDelete('cascade')->nullable();
            $table->bigInteger('custom_point_id')->nullable()->default(null);
            $table->unsignedInteger('instance_number')->default(1);
            $table->foreignId('mob_id')->references('id')->on('mobs')->onDelete('cascade')->nullable()->default(null);
            $table->decimal('x',3,1)->nullable()->default(null);
            $table->decimal('y',3,1)->nullable()->default(null);
            $table->timestamps();
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
