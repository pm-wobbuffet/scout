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
        Schema::create('scout_scouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts')->onDelete('cascade');
            $table->string('scout_name', 30);
            $table->unique(['scout_id', 'scout_name']);
        });

        Schema::table('scouts', function (Blueprint $table) {
            $table->dropColumn('scouts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_scouts');
        Schema::table('scouts', function (Blueprint $table) {
            $table->json('scouts')->nullable()->default(null);
        });
    }
};
