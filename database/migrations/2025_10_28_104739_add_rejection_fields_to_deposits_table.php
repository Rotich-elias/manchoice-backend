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
        Schema::table('deposits', function (Blueprint $table) {
            // Add 'rejected' status to the enum
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed', 'rejected'])
                ->default('pending')
                ->change();

            // Add rejection tracking fields
            $table->text('rejection_reason')->nullable()->after('notes');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('rejected_at');
            $table->integer('rejection_count')->default(0)->after('rejected_by');

            // Add index for better query performance
            $table->index('rejected_by');
            $table->index(['loan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['rejected_by']);
            $table->dropIndex(['loan_id', 'status']);

            // Drop rejection fields
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['rejection_reason', 'rejected_at', 'rejected_by', 'rejection_count']);

            // Revert status enum (note: this might fail if rejected records exist)
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])
                ->default('pending')
                ->change();
        });
    }
};
