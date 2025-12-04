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
            $table->bigInteger('version')->nullable()->default(null);
            $table->timestamps();

            // Indexes
            $table->unique(['scout_id', 'version']);
        });

        // Create trigger to auto increment the version column
        DB::unprepared("
        CREATE TRIGGER `before_insert_version_trigger` 
        BEFORE INSERT ON `scout_versions` 
        FOR EACH ROW 
        BEGIN
            DECLARE new_version_id INT;
            SET new_version_id = (SELECT COALESCE(MAX(`version`), 0) + 1 FROM
            scout_versions WHERE scout_id = NEW.scout_id);
            SET NEW.version = new_version_id;
        END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_versions');
    }
};
