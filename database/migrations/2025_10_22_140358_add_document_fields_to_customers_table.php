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
        Schema::table('customers', function (Blueprint $table) {
            // Add document photo paths to customers table
            // These documents will be stored on the customer profile and reused for loan applications
            $table->text('bike_photo_path')->nullable()->after('guarantor_passport_photo_path');
            $table->text('logbook_photo_path')->nullable()->after('bike_photo_path');
            $table->text('passport_photo_path')->nullable()->after('logbook_photo_path');
            $table->text('id_photo_front_path')->nullable()->after('passport_photo_path');
            $table->text('id_photo_back_path')->nullable()->after('id_photo_front_path');
            $table->text('next_of_kin_id_front_path')->nullable()->after('id_photo_back_path');
            $table->text('next_of_kin_id_back_path')->nullable()->after('next_of_kin_id_front_path');
            $table->text('guarantor_id_front_path')->nullable()->after('next_of_kin_id_back_path');
            $table->text('guarantor_id_back_path')->nullable()->after('guarantor_id_front_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'bike_photo_path',
                'logbook_photo_path',
                'passport_photo_path',
                'id_photo_front_path',
                'id_photo_back_path',
                'next_of_kin_id_front_path',
                'next_of_kin_id_back_path',
                'guarantor_id_front_path',
                'guarantor_id_back_path',
            ]);
        });
    }
};
