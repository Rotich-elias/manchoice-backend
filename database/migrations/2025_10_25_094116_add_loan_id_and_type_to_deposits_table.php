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
            // loan_id already exists, only add type column
            if (!Schema::hasColumn('deposits', 'type')) {
                $table->enum('type', ['registration', 'loan_deposit', 'savings'])->default('registration')->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Only drop type column, keep loan_id as it existed before
            if (Schema::hasColumn('deposits', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
