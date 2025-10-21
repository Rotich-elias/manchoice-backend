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
        Schema::table('loans', function (Blueprint $table) {
            // Rename existing id_photo_path to id_photo_front_path
            $table->renameColumn('id_photo_path', 'id_photo_front_path');
        });

        Schema::table('loans', function (Blueprint $table) {
            // Add id_photo_back_path after id_photo_front_path
            $table->text('id_photo_back_path')->nullable()->after('id_photo_front_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Drop the back photo field
            $table->dropColumn('id_photo_back_path');
        });

        Schema::table('loans', function (Blueprint $table) {
            // Rename back to original name
            $table->renameColumn('id_photo_front_path', 'id_photo_path');
        });
    }
};
