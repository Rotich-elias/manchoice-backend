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
        // Modify the status enum to include 'awaiting_deposit'
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('awaiting_registration_fee', 'awaiting_deposit', 'pending', 'approved', 'active', 'completed', 'defaulted', 'cancelled', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('awaiting_registration_fee', 'pending', 'approved', 'active', 'completed', 'defaulted', 'cancelled', 'rejected') DEFAULT 'pending'");
    }
};
