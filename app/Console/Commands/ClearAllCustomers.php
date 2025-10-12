<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ClearAllCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:clear {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all customers and their associated loans and payments from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get count before deletion
        $customerCount = Customer::count();
        $loanCount = Loan::count();
        $paymentCount = Payment::count();

        if ($customerCount === 0 && $loanCount === 0 && $paymentCount === 0) {
            $this->info('No customers, loans, or payments to delete.');
            return 0;
        }

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            $message = "Are you sure you want to delete:\n";
            $message .= "- {$customerCount} customers\n";
            $message .= "- {$loanCount} loans\n";
            $message .= "- {$paymentCount} payments\n";
            $message .= "This action cannot be undone.";

            if (!$this->confirm($message)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("Deleting all customer data...");

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete in order: payments -> loans -> customers
            Payment::truncate();
            $this->info('✓ Cleared all payments');

            Loan::truncate();
            $this->info('✓ Cleared all loans');

            Customer::truncate();
            $this->info('✓ Cleared all customers');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info("Successfully deleted {$customerCount} customers, {$loanCount} loans, and {$paymentCount} payments.");

            return 0;
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
