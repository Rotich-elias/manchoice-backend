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
        // Check if columns already exist and add them if they don't
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            });
        }

        if (!Schema::hasColumn('users', 'approval_limit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('approval_limit', 12, 2)->nullable()->after('status')->comment('Loan approval limit for managers');
            });
        }

        if (!Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('approval_limit');
            });
        }

        if (!Schema::hasColumn('users', 'created_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('last_login_at')->constrained('users')->nullOnDelete();
            });
        }

        // Temporarily change role to string so we can update values
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'customer'");

        // Update role values to match new enum
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'user')->update(['role' => 'customer']);

        // Now change role to enum with all the new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'clerk', 'collector', 'customer') NOT NULL DEFAULT 'customer'");

        // Add indexes if they don't exist
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
            if (!$this->hasIndex('users', 'users_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('users', 'users_created_by_index')) {
                $table->index('created_by');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex($table, $index)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $idx) {
            if ($idx->Key_name === $index) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);

            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'status',
                'approval_limit',
                'last_login_at',
                'created_by',
            ]);

            // Revert role back to original
            $table->enum('role', ['admin', 'customer'])->default('customer')->change();
        });
    }
};
