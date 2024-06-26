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
        Schema::create('custom_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id');
            $table->foreignId('zone_id');
            $table->bigInteger('point_id')->nullable()->default(null);
            $table->foreignId('mob_id')->nullable()->default(null);
            $table->text('line_source')->nullable()->default(null);
            $table->decimal('x')->nullable()->default(null);
            $table->decimal('y')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_points');
    }
};
