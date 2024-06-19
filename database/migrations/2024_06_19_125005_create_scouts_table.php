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
        Schema::create('scouts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('collaborator_password')->nullable()->default(null);
            $table->json('instance_data')->nullable()->default(null);
            $table->json('point_data')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('finalized_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scouts');
    }
};
