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
        Schema::create('aetherytes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->decimal('x');
            $table->decimal('y');
            $table->string('name');
            $table->unsignedInteger('icon')->default(60453);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aetherytes');
    }
};
