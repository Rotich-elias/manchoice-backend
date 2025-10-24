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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('registration_fee_paid')->default(false)->after('profile_completed');
            $table->decimal('registration_fee_amount', 10, 2)->nullable()->after('registration_fee_paid');
            $table->timestamp('registration_fee_paid_at')->nullable()->after('registration_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_fee_paid', 'registration_fee_amount', 'registration_fee_paid_at']);
        });
    }
};
