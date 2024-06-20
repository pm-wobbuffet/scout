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
        Schema::create('scout_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id');
            $table->decimal('x');
            $table->decimal('y');
            $table->string('mob_index');
            $table->foreignId('zone_id');
            $table->unsignedInteger('instance_number')->default(1);
            $table->bigInteger('point_id')->nullable()->default(null);
            $table->json('previous_instance_data')->nullable()->default(null);
            $table->json('previous_point_data')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_updates');
    }
};
