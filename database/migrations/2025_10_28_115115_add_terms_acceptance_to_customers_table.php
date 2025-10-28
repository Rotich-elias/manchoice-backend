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
            $table->boolean('accepted_terms')->default(false)->after('notes');
            $table->timestamp('accepted_terms_at')->nullable()->after('accepted_terms');
            $table->string('accepted_terms_version', 20)->default('1.0')->after('accepted_terms_at');
            $table->string('accepted_terms_ip', 45)->nullable()->after('accepted_terms_version');

            $table->index('accepted_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['accepted_terms']);
            $table->dropColumn([
                'accepted_terms',
                'accepted_terms_at',
                'accepted_terms_version',
                'accepted_terms_ip',
            ]);
        });
    }
};
