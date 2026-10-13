<?php

namespace App\Console\Commands;

use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;

class FixZeroDebtBug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-zero-debt-bug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix zero debt bug';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->ask('Enter the date of the last payment (YYYY-MM-DD)');

        $this->info('Fixing zero debt bug...');

        $studentGroupPayments = StudentGroupPayment::where('created_at', '>=', $date)
            ->where('amount', 0)
            ->get();

        foreach ($studentGroupPayments as $studentGroupPayment) {
            $student = $studentGroupPayment->student;
            if ($student) {
                $student->groups()->updateExistingPivot($studentGroupPayment->group_id, ['amount' => null]);
            }

            $studentGroupPayment->delete();
        }

        $this->call('app:daily-review-command');

        $this->info('Zero debt bug fixed!');
    }
}
