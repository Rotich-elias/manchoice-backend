<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClearAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clear {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all users and their associated tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get count before deletion
        $userCount = User::count();

        if ($userCount === 0) {
            $this->info('No users to delete.');
            return 0;
        }

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete all {$userCount} users? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("Deleting {$userCount} users...");

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete all personal access tokens
            DB::table('personal_access_tokens')->truncate();
            $this->info('✓ Cleared all access tokens');

            // Delete all users
            User::truncate();
            $this->info('✓ Cleared all users');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info("Successfully deleted {$userCount} users and their tokens.");

            return 0;
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
