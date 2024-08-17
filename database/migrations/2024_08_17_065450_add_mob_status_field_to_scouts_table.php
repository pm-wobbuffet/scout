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
        Schema::table('scouts', function (Blueprint $table) {
            $table->json('mob_status')->nullable()->default(null);
        });
        DB::update('UPDATE scouts SET mob_status = "{}" WHERE mob_status IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scouts', function (Blueprint $table) {
            $table->dropColumn('mob_status');
        });
    }
};
