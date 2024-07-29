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
        Schema::table('scouts', function (Blueprint $table) {
            $table->string('title', 100)->nullable()->default(null);
            $table->json('scouts')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scouts', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('scouts');
        });
    }
};
