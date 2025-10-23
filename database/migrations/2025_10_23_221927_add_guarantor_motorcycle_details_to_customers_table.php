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
            // Guarantor Motorcycle Details
            $table->string('guarantor_motorcycle_number_plate')->nullable()->after('guarantor_relationship');
            $table->string('guarantor_motorcycle_chassis_number')->nullable()->after('guarantor_motorcycle_number_plate');
            $table->string('guarantor_motorcycle_model')->nullable()->after('guarantor_motorcycle_chassis_number');
            $table->string('guarantor_motorcycle_type')->nullable()->after('guarantor_motorcycle_model');
            $table->string('guarantor_motorcycle_engine_cc')->nullable()->after('guarantor_motorcycle_type');
            $table->string('guarantor_motorcycle_colour')->nullable()->after('guarantor_motorcycle_engine_cc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'guarantor_motorcycle_number_plate',
                'guarantor_motorcycle_chassis_number',
                'guarantor_motorcycle_model',
                'guarantor_motorcycle_type',
                'guarantor_motorcycle_engine_cc',
                'guarantor_motorcycle_colour',
            ]);
        });
    }
};
