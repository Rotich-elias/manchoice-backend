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
            $table->text('next_of_kin_passport_photo_path')->nullable()->after('next_of_kin_id_photo_path');
            $table->text('guarantor_passport_photo_path')->nullable()->after('guarantor_id_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'next_of_kin_passport_photo_path',
                'guarantor_passport_photo_path',
            ]);
        });
    }
};
