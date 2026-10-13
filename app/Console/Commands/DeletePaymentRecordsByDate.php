<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentDiscount;
use App\Models\RefundPayment;
use App\Models\StudentGroupPayment;
use App\Models\Transaction;
use Illuminate\Console\Command;

class DeletePaymentRecordsByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-payment-records-by-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete payment records by date range.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->ask('Enter the start date (YYYY-MM-DD)');
        $endDate = $this->ask('Enter the end date (YYYY-MM-DD)');
        // Fetch payments that need to be processed
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->with(['student_group_payment', 'transaction', 'refunds', 'payment_discounts'])->get();

        // Initialize arrays to store data for bulk operations
        $studentGroupPaymentsToUpdate = [];
        $transactionsToDelete = [];
        $refundsToDelete = [];
        $discountsToDelete = [];

        // Process each payment
        foreach ($payments as $payment) {
            // Update student group payments' left_amount
            if ($payment->student_group_payment_id) {
                $studentGroupPaymentsToUpdate[$payment->student_group_payment_id] = ($studentGroupPaymentsToUpdate[$payment->student_group_payment_id] ?? 0) + $payment->amount;
            }

            // Collect transactions to delete
            if ($payment->transaction_id) {
                $transactionsToDelete[] = $payment->transaction_id;
            }

            // Collect refunds to delete
            $refundsToDelete = array_merge($refundsToDelete, $payment->refunds->pluck('id')->toArray());

            // Collect discounts to delete
            $discountsToDelete = array_merge($discountsToDelete, $payment->payment_discounts->pluck('id')->toArray());

            // Delete the payment
            $payment->delete();
        }

        // Bulk update student group payments' left_amount
        foreach ($studentGroupPaymentsToUpdate as $studentGroupPaymentId => $amount) {
            $studentGroupPayment = StudentGroupPayment::find($studentGroupPaymentId);
            if ($studentGroupPayment) {
                $studentGroupPayment->left_amount += $amount;
                $studentGroupPayment->save();
            }
        }

        // Bulk delete transactions
        Transaction::whereIn('id', $transactionsToDelete)->delete();

        // Bulk delete refunds
        RefundPayment::whereIn('id', $refundsToDelete)->delete();

        // Bulk delete discounts
        PaymentDiscount::whereIn('id', $discountsToDelete)->delete();

        $this->info('Payment records have been deleted successfully. ✅');
    }
}
