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
            // Next of Kin additional fields
            $table->string('next_of_kin_email')->nullable()->after('next_of_kin_relationship');
            $table->text('next_of_kin_passport_photo_path')->nullable()->after('next_of_kin_email');

            // Guarantor additional fields
            $table->string('guarantor_email')->nullable()->after('guarantor_relationship');
            $table->text('guarantor_passport_photo_path')->nullable()->after('guarantor_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'next_of_kin_email',
                'next_of_kin_passport_photo_path',
                'guarantor_email',
                'guarantor_passport_photo_path',
            ]);
        });
    }
};
