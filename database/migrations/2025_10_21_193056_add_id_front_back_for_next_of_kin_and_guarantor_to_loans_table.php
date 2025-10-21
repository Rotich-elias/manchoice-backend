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
        // Rename next of kin ID photo to front
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('next_of_kin_id_photo_path', 'next_of_kin_id_front_path');
        });

        // Add next of kin ID back photo
        Schema::table('loans', function (Blueprint $table) {
            $table->text('next_of_kin_id_back_path')->nullable()->after('next_of_kin_id_front_path');
        });

        // Rename guarantor ID photo to front
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('guarantor_id_photo_path', 'guarantor_id_front_path');
        });

        // Add guarantor ID back photo
        Schema::table('loans', function (Blueprint $table) {
            $table->text('guarantor_id_back_path')->nullable()->after('guarantor_id_front_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop guarantor ID back photo
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('guarantor_id_back_path');
        });

        // Rename guarantor ID front back to original
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('guarantor_id_front_path', 'guarantor_id_photo_path');
        });

        // Drop next of kin ID back photo
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('next_of_kin_id_back_path');
        });

        // Rename next of kin ID front back to original
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('next_of_kin_id_front_path', 'next_of_kin_id_photo_path');
        });
    }
};
