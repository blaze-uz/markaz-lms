<?php

namespace App\Console\Commands;

use App\Models\StudentGroupPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStudentGroupPaymentsCommand extends Command
{
    protected $signature = 'payments:update-left-amount';
    protected $description = 'Update left amount values for student group payments within a date range';

    public function handle()
    {
        $startDate = $this->ask('Enter start date (Y-m-d)');
        $endDate = $this->ask('Enter end date (Y-m-d)');
        $rangeStartDate = $this->ask('Enter range start date (Y-m-d)');
        $rangeEndDate = $this->ask('Enter range end date (Y-m-d)');
        $leftAmount = $this->ask('Enter amount value to update');

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $rangeStartDate = Carbon::parse($rangeStartDate)->format('Y-m-d');
            $rangeEndDate = Carbon::parse($rangeEndDate)->format('Y-m-d');
            $range = $rangeStartDate . '/' . $rangeEndDate;
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use Y-m-d format (e.g., 2023-12-26)');
            return;
        }

        if (!is_numeric($leftAmount)) {
            $this->error('Amount must be a number');
            return;
        }

        if (!$this->confirm("This will update amount and left_amount to {$leftAmount} for all payments between {$startDate->format('Y-m-d')} and {$endDate->format('Y-m-d')} with range {$range}. Continue?")) {
            $this->info('Operation cancelled.');
            return;
        }

        DB::beginTransaction();
        try {
            $payments = StudentGroupPayment::query()
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('range', $range)
                ->get();

            if ($payments->isEmpty()) {
                $this->warn('No payments found with the specified criteria.');
                return;
            }

            $total = $payments->count();
            $this->info("Found {$total} payments to update.");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($payments as $payment) {
                $payment->amount = $leftAmount;
                $payment->left_amount = $leftAmount;
                $payment->save();
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine();
            $this->info('Successfully updated amount and left_amount values for all payments in the specified date range.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
