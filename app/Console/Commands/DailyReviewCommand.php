<?php

namespace App\Console\Commands;

use App\Jobs\CalculateDebtForStudent;
use App\Jobs\PayWithBalance;
use App\Jobs\UserContract;
use Illuminate\Console\Command;

class DailyReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-review-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily review command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create a new progress bar with 3 steps
        $bar = $this->output->createProgressBar(3);

        CalculateDebtForStudent::dispatch();
        $bar->advance(); // Advance the progress bar by one step

        PayWithBalance::dispatch();
        $bar->advance(); // Advance the progress bar by one step

        UserContract::dispatch();
        $bar->advance(); // Advance the progress bar by one step

        // Ensure that the progress bar is at 100%
        $bar->finish();

        $this->info("\nDaily review command completed!");
    }
}
