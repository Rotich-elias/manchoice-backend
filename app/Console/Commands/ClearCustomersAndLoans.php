<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearCustomersAndLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clear {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all customers, loans, and payments data for fresh start';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will DELETE ALL customers, loans, and payments. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting data cleanup...');

        DB::beginTransaction();

        try {
            // Count before deletion
            $customerCount = Customer::count();
            $loanCount = Loan::count();
            $paymentCount = Payment::count();

            $this->info("Found: {$customerCount} customers, {$loanCount} loans, {$paymentCount} payments");

            // Delete payments first (foreign key constraint)
            Payment::query()->forceDelete();
            $this->info('✓ Cleared all payments');

            // Delete loans
            Loan::query()->forceDelete();
            $this->info('✓ Cleared all loans');

            // Delete customers
            Customer::query()->forceDelete();
            $this->info('✓ Cleared all customers');

            // Clear uploaded documents from storage
            if (Storage::disk('public')->exists('documents')) {
                Storage::disk('public')->deleteDirectory('documents');
                Storage::disk('public')->makeDirectory('documents');
                $this->info('✓ Cleared all uploaded documents');
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Database cleaned successfully!');
            $this->info("Deleted: {$customerCount} customers, {$loanCount} loans, {$paymentCount} payments");
            $this->newLine();
            $this->warn('You can now start fresh with the new guarantor motorcycle fields.');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error cleaning database: ' . $e->getMessage());
            return 1;
        }
    }
}
