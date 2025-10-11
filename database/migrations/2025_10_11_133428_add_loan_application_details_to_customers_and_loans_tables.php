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
        // Add motorcycle and application details to customers table
        Schema::table('customers', function (Blueprint $table) {
            // Motorcycle Details
            $table->string('motorcycle_number_plate')->nullable()->after('notes');
            $table->string('motorcycle_chassis_number')->nullable()->after('motorcycle_number_plate');
            $table->string('motorcycle_model')->nullable()->after('motorcycle_chassis_number');
            $table->string('motorcycle_type')->nullable()->after('motorcycle_model');
            $table->string('motorcycle_engine_cc')->nullable()->after('motorcycle_type');
            $table->string('motorcycle_colour')->nullable()->after('motorcycle_engine_cc');

            // Next of Kin Details
            $table->string('next_of_kin_name')->nullable()->after('motorcycle_colour');
            $table->string('next_of_kin_phone')->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_relationship')->nullable()->after('next_of_kin_phone');

            // Guarantor Details
            $table->string('guarantor_name')->nullable()->after('next_of_kin_relationship');
            $table->string('guarantor_phone')->nullable()->after('guarantor_name');
            $table->string('guarantor_relationship')->nullable()->after('guarantor_phone');
        });

        // Add photo storage fields to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->text('bike_photo_path')->nullable()->after('notes');
            $table->text('logbook_photo_path')->nullable()->after('bike_photo_path');
            $table->text('passport_photo_path')->nullable()->after('logbook_photo_path');
            $table->text('id_photo_path')->nullable()->after('passport_photo_path');
            $table->text('next_of_kin_id_photo_path')->nullable()->after('id_photo_path');
            $table->text('guarantor_id_photo_path')->nullable()->after('next_of_kin_id_photo_path');
            $table->text('application_details')->nullable()->after('guarantor_id_photo_path'); // JSON field for additional details
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'motorcycle_number_plate',
                'motorcycle_chassis_number',
                'motorcycle_model',
                'motorcycle_type',
                'motorcycle_engine_cc',
                'motorcycle_colour',
                'next_of_kin_name',
                'next_of_kin_phone',
                'next_of_kin_relationship',
                'guarantor_name',
                'guarantor_phone',
                'guarantor_relationship',
            ]);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'bike_photo_path',
                'logbook_photo_path',
                'passport_photo_path',
                'id_photo_path',
                'next_of_kin_id_photo_path',
                'guarantor_id_photo_path',
                'application_details',
            ]);
        });
    }
};
