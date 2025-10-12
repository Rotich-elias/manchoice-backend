<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status column to include 'rejected'
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'approved', 'active', 'completed', 'defaulted', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'approved', 'active', 'completed', 'defaulted', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
