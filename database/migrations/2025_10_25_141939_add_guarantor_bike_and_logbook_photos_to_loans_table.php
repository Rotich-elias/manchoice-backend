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
            // Add guarantor bike and logbook photo paths after guarantor_passport_photo_path
            $table->text('guarantor_bike_photo_path')->nullable()->after('guarantor_passport_photo_path');
            $table->text('guarantor_logbook_photo_path')->nullable()->after('guarantor_bike_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['guarantor_bike_photo_path', 'guarantor_logbook_photo_path']);
        });
    }
};
