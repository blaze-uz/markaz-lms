<?php

namespace App\Console\Commands;

use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;

class RemoveStudentGroupPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-student-group-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove student group payments with left amount equal to amount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //ask range
        $range = $this->ask('Enter range (e.g. 1/12, 2024-01-01/2024-02-01):');
        
        //ask group (optional)
        $group = $this->ask('Enter group ID (optional, press enter to skip):');
        
        $query = StudentGroupPayment::where('range', $range);
        
        // Apply group filter if provided
        if ($group) {
            $query->where('group_id', $group);
            $this->info("Filtering by group ID: $group");
        }
        
        $student_group_payments = $query->get();

        $count = 0;

        foreach ($student_group_payments as $student_group_payment) {
            if ($student_group_payment->amount == $student_group_payment->left_amount) {
                $count++;
                $student_group_payment->delete();
            }
        }

        $this->info('Removed ' . $count . ' student group payments');
    }
}
