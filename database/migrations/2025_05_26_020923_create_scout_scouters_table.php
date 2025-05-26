<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scout_scouters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scout_id')->references('id')->on('scouts')->onDelete('cascade');
            $table->string('scout_name', 30);
            $table->unique(['scout_id', 'scout_name']);
        });

        DB::unprepared('ALTER TABLE scouts DROP CHECK scouts_chk_4');
        Schema::table('scouts', function (Blueprint $table) {
            $table->renameColumn('scouts', 'scouts_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_scouters');
        Schema::table('scouts', function (Blueprint $table) {
            $table->renameColumn('scouts_old', 'scouts');
        });
    }
};
