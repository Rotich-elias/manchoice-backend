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
            $table->decimal('deposit_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('deposit_paid', 15, 2)->default(0)->after('deposit_amount');
            $table->boolean('deposit_required')->default(true)->after('deposit_paid');
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'deposit_paid', 'deposit_required', 'deposit_paid_at']);
        });
    }
};
