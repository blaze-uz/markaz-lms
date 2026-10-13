<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshAccountBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-account-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh account balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = \App\Models\Account::all();

        // Create a new progress bar with the total number of accounts
        $bar = $this->output->createProgressBar(count($accounts));

        foreach ($accounts as $account) {
            $account->balance = $account->getItemBalance();
            $account->save();

            // Advance the progress bar by one step
            $bar->advance();
        }

        // Ensure that the progress bar is at 100%
        $bar->finish();

        $this->info("\nAccount balance refreshed successfully.");
    }
}
