<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ClearAllLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:clear {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all loans and their associated payments from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get count before deletion
        $loanCount = Loan::count();
        $paymentCount = Payment::count();

        if ($loanCount === 0 && $paymentCount === 0) {
            $this->info('No loans or payments to delete.');
            return 0;
        }

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete all {$loanCount} loans and {$paymentCount} payments? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("Deleting {$loanCount} loans and {$paymentCount} payments...");

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete all payments first (they reference loans)
            Payment::truncate();
            $this->info('✓ Cleared all payments');

            // Delete all loans
            Loan::truncate();
            $this->info('✓ Cleared all loans');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info("Successfully deleted {$loanCount} loans and {$paymentCount} payments.");

            return 0;
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
