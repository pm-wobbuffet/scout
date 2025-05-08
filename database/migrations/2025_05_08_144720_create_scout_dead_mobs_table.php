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
        Schema::create('scout_dead_mobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts')->onDelete('cascade');
            $table->foreignId('mob_id')->references('id')->on('mobs')->onDelete('cascade');
            $table->unsignedInteger('instance_number')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_dead_mobs');
    }
};
