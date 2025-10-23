<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\PaymentSchedule;
use Illuminate\Console\Command;

class UpdateLoanStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update loan statuses based on payment schedules and mark defaulted loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating payment schedules...');
        $this->updatePaymentSchedules();

        $this->info('Checking for defaulted loans...');
        $this->checkDefaultedLoans();

        $this->info('Loan status update completed!');
        return 0;
    }

    /**
     * Update payment schedule statuses based on due dates
     */
    private function updatePaymentSchedules(): void
    {
        $updated = 0;

        // Mark overdue schedules
        PaymentSchedule::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $overdueCount = PaymentSchedule::where('status', 'overdue')->count();

        // Mark partial payments
        PaymentSchedule::where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'expected_amount')
            ->update(['status' => 'partial']);

        $partialCount = PaymentSchedule::where('status', 'partial')->count();

        // Mark fully paid
        PaymentSchedule::whereColumn('paid_amount', '>=', 'expected_amount')
            ->where('status', '!=', 'paid')
            ->update(['status' => 'paid']);

        $paidCount = PaymentSchedule::where('status', 'paid')->count();

        $this->info("Payment schedules updated:");
        $this->info("  - Overdue: {$overdueCount}");
        $this->info("  - Partial: {$partialCount}");
        $this->info("  - Paid: {$paidCount}");
    }

    /**
     * Check and mark loans as defaulted based on missed payments
     */
    private function checkDefaultedLoans(): void
    {
        $loans = Loan::whereIn('status', ['approved', 'active'])
            ->with('paymentSchedule')
            ->get();

        $defaultedCount = 0;

        foreach ($loans as $loan) {
            if ($loan->shouldBeDefaulted()) {
                $missedPayments = $loan->getMissedPaymentsCount();
                $overdueAmount = $loan->getOverdueAmount();

                $loan->markAsDefaulted(
                    "Missed {$missedPayments} payments. Overdue amount: KES " . number_format($overdueAmount, 2)
                );

                $defaultedCount++;
                $this->warn("Loan {$loan->loan_number} marked as DEFAULTED ({$missedPayments} missed payments)");
            }
        }

        $this->info("Total loans marked as defaulted: {$defaultedCount}");
    }
}
