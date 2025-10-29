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
        // Add penalty fields to payment_schedules table
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->decimal('penalty_amount', 10, 2)->default(0)->after('paid_amount');
            $table->boolean('penalty_applied')->default(false)->after('penalty_amount');
            $table->timestamp('penalty_applied_date')->nullable()->after('penalty_applied');
        });

        // Add total penalty tracking to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('total_penalty_amount', 10, 2)->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropColumn(['penalty_amount', 'penalty_applied', 'penalty_applied_date']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('total_penalty_amount');
        });
    }
};
