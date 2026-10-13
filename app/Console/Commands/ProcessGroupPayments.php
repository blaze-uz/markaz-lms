<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GroupBalancePaymentProcessor;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessGroupPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-group-payments {--dry-run : Run without making actual changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process group payments for all users who have groups with balances';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode - no actual changes will be made');
        }

        $this->info('Starting group payment processing...');

        $causer = User::query()->whereHas('roles')->first() ?? User::query()->first();
        if (! $causer) {
            $this->error('No user found to attribute group payment transactions to.');

            return self::FAILURE;
        }

        try {
            $summary = (new GroupBalancePaymentProcessor)->process($causer->id, $dryRun);
        } catch (Exception $e) {
            $this->error('Error processing group payments: '.$e->getMessage());
            Log::error('Error processing group payments', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        foreach ($summary['processed'] as $row) {
            $this->line(
                ($dryRun ? 'Would process' : 'Processed')
                ." group {$row['group_id']} - Amount: "
                .number_format((int) $row['amount'])
            );
        }

        foreach ($summary['skipped'] as $row) {
            $this->warn("Skipped group {$row['group_id']} - Amount: ".number_format((int) $row['amount']));
        }

        $this->info('Processing completed:');
        $this->info("- Found groups: {$summary['groups_count']}");
        $this->info("- Processed: {$summary['processed_count']} groups");
        $this->info("- Skipped: {$summary['skipped_count']} groups");
        $this->info('- Total amount processed: '.number_format($summary['total_amount']));

        if ($dryRun) {
            $this->warn('This was a dry run - no actual changes were made');
        }

        return self::SUCCESS;
    }
}
