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
        Schema::create('scout_versions', function (Blueprint $table) {
            // Columns
            $table->id();
            $table->foreignId('scout_id')->index();
            $table->string('user', 60)->nullable()->default(null);
            $table->longText('scout_details');
            $table->text('update_details')->nullable()->default(null);
            $table->bigInteger('version')->nullable()->default(1);
            $table->timestamps();

            // Indexes
            $table->unique(['id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_versions');
    }
};
